<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class DatadogLicenseGenerator
{
    private const DASHBOARD_TEMPLATE = 'templates/dashboards/license-dashboard-template.json';
    private const DASHBOARD_NOTE_ID = 396927851126963;
    private const LICENSE_ORDER = [
        'infra_host', 'container', 'cnm', 'ndm', 'custom_metrics', 'custom_events',
        'apm_host', 'apm_ingested_spans', 'apm_indexed_spans', 'apm_profiler_host', 'apm_profiler_container',
        'dbm_host', 'rum_sessions', 'rum_measure', 'rum_investigate', 'rum_session_replay',
        'logs_ingested', 'logs_indexed', 'cloud_siem', 'synthetic_api', 'synthetic_browser',
    ];

    public function __construct(private readonly TemplateRenderer $renderer)
    {
    }

    public function generate(array $input): array
    {
        $licenses = config('datadog.licenses', []);
        $effective = $this->buildEffectiveLicenses($input, $licenses);

        $validator = Validator::make($input, [
            'infra_host' => ['nullable', 'numeric', 'gt:0'],
            'infra_plan' => ['nullable', 'in:pro,enterprise'],
            'container_addon' => ['nullable', 'numeric', 'min:0'],
            'ndm' => ['nullable', 'numeric', 'gt:0'],
            'cnm' => ['nullable', 'numeric', 'gt:0'],
            'apm_host' => ['nullable', 'numeric', 'gt:0'],
            'apm_plan' => ['nullable', 'in:pro,enterprise'],
            'apm_ingested_addon_gb' => ['nullable', 'numeric', 'min:0'],
            'apm_indexed_addon_million' => ['nullable', 'numeric', 'min:0'],
            'dbm_host' => ['nullable', 'numeric', 'gt:0'],
            'rum_mode' => ['nullable', 'in:session,without_limit'],
            'rum_sessions' => ['nullable', 'numeric', 'gt:0'],
            'rum_investigate' => ['nullable', 'numeric', 'gt:0'],
            'rum_measure' => ['nullable', 'numeric', 'gt:0'],
            'rum_session_replay' => ['nullable', 'numeric', 'gt:0'],
            'synthetic_api' => ['nullable', 'numeric', 'gt:0'],
            'synthetic_browser' => ['nullable', 'numeric', 'gt:0'],
            'logs_ingested' => ['nullable', 'numeric', 'gt:0'],
            'logs_indexed' => ['nullable', 'numeric', 'gt:0'],
            'cloud_siem' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $validator->after(function ($validator) use ($input) {
            $hasAny = false;
            foreach ($this->primaryInputFields() as $field) {
                if (isset($input[$field]) && $input[$field] !== '' && (float) $input[$field] > 0) {
                    $hasAny = true;
                    break;
                }
            }
            if (!$hasAny) {
                $validator->errors()->add('licenses', 'Select at least one license or plan.');
            }

            if (!empty($input['infra_host']) && empty($input['infra_plan'])) {
                $validator->errors()->add('infra_plan', 'Choose an Infra Host plan.');
            }
            if (!empty($input['apm_host']) && empty($input['apm_plan'])) {
                $validator->errors()->add('apm_plan', 'Choose an APM Host plan.');
            }

            if (!empty($input['rum_sessions']) || !empty($input['rum_investigate']) || !empty($input['rum_measure']) || !empty($input['rum_session_replay'])) {
                if (empty($input['rum_mode'])) {
                    $validator->errors()->add('rum_mode', 'Choose RUM Session or RUM Without Limit.');
                }
                if (($input['rum_mode'] ?? null) === 'session' && (!empty($input['rum_investigate']) || !empty($input['rum_measure']))) {
                    $validator->errors()->add('rum_mode', 'RUM Session cannot contain RUM Investigate or RUM Measure.');
                }
                if (($input['rum_mode'] ?? null) === 'without_limit' && !empty($input['rum_sessions'])) {
                    $validator->errors()->add('rum_mode', 'RUM Without Limit cannot contain RUM Session.');
                }
            }
        });

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->toJson());
        }

        $dashboard = $this->buildDashboard($effective, $licenses);
        $monitors = [];
        $monitorFiles = [];
        $summary = [];

        foreach ($effective as $code => $commitment) {
            $definition = $licenses[$code];
            $rawCommitment = $commitment * (float) ($definition['multiplier'] ?? 1);
            $monitor = $this->renderer->renderJsonTemplate(
                resource_path('templates/monitors/license-usage.json'),
                [
                    'license_name' => $definition['name'],
                    'monitor_query' => $definition['monitor_query'],
                    'critical_value' => $this->formatNumber($rawCommitment),
                ]
            );
            $monitorJson = json_encode(
                $monitor,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
            $monitors[] = $monitor;
            $monitorFiles[] = [
                'code' => $code,
                'name' => $definition['name'],
                'filename' => $this->monitorFilename($definition['name']),
                'json' => $monitorJson,
            ];

            $summary[] = [
                'code' => $code,
                'name' => $definition['name'],
                'commitment' => $commitment,
                'unit' => $definition['input_unit'],
                'source' => $this->isDerivedLicense($code, $input) ? 'Derived from plan' : 'Entered by user',
            ];
        }

        return [
            'dashboard' => $dashboard,
            'monitors' => $monitors,
            'monitor_files' => $monitorFiles,
            'summary' => $summary,
            'settings' => [
                'infra_plan' => $input['infra_plan'] ?? null,
                'apm_plan' => $input['apm_plan'] ?? null,
                'rum_mode' => $input['rum_mode'] ?? null,
            ],
        ];
    }

    private function primaryInputFields(): array
    {
        return ['infra_host', 'ndm', 'cnm', 'apm_host', 'dbm_host', 'rum_sessions', 'rum_investigate', 'rum_measure', 'rum_session_replay', 'synthetic_api', 'synthetic_browser', 'logs_ingested', 'logs_indexed', 'cloud_siem'];
    }

    private function buildEffectiveLicenses(array $input, array $licenses): array
    {
        $effective = [];

        if (!empty($input['infra_host'])) {
            $hosts = (float) $input['infra_host'];
            $effective['infra_host'] = $hosts;
            $plan = $input['infra_plan'] ?? 'pro';
            $container = $hosts * ($plan === 'enterprise' ? 10 : 5) + (float) ($input['container_addon'] ?? 0);
            $effective['container'] = $container;
            $effective['custom_metrics'] = $hosts * ($plan === 'enterprise' ? 200 : 100);
            $effective['custom_events'] = $hosts * ($plan === 'enterprise' ? 1000 : 500);
        }

        foreach (['ndm', 'cnm', 'dbm_host'] as $field) {
            if (!empty($input[$field])) {
                $effective[$field] = (float) $input[$field];
            }
        }

        if (!empty($input['apm_host'])) {
            $hosts = (float) $input['apm_host'];
            $effective['apm_host'] = $hosts;
            $effective['apm_ingested_spans'] = $hosts * 150 + (float) ($input['apm_ingested_addon_gb'] ?? 0);
            $effective['apm_indexed_spans'] = $hosts * 1 + (float) ($input['apm_indexed_addon_million'] ?? 0);
            if (($input['apm_plan'] ?? 'pro') === 'enterprise') {
                $effective['apm_profiler_host'] = $hosts;
                $effective['apm_profiler_container'] = $hosts * 4;
            }
        }

        $rumMode = $input['rum_mode'] ?? null;
        if ($rumMode === 'session') {
            if (!empty($input['rum_sessions'])) $effective['rum_sessions'] = (float) $input['rum_sessions'];
            if (!empty($input['rum_session_replay'])) $effective['rum_session_replay'] = (float) $input['rum_session_replay'];
        } elseif ($rumMode === 'without_limit') {
            foreach (['rum_investigate', 'rum_measure', 'rum_session_replay'] as $field) {
                if (!empty($input[$field])) $effective[$field] = (float) $input[$field];
            }
        }

        foreach (['synthetic_api', 'synthetic_browser', 'logs_ingested', 'logs_indexed', 'cloud_siem'] as $field) {
            if (!empty($input[$field])) $effective[$field] = (float) $input[$field];
        }

        $effective = array_intersect_key($effective, $licenses);
        return $this->orderLicenses($effective);
    }

    private function orderLicenses(array $licenses): array
    {
        $ordered = [];
        foreach (self::LICENSE_ORDER as $code) {
            if (array_key_exists($code, $licenses)) {
                $ordered[$code] = $licenses[$code];
            }
        }
        foreach ($licenses as $code => $value) {
            if (!array_key_exists($code, $ordered)) {
                $ordered[$code] = $value;
            }
        }
        return $ordered;
    }

    private function buildDashboard(array $effective, array $licenses): array
    {
        $path = resource_path(self::DASHBOARD_TEMPLATE);
        $json = file_get_contents($path);
        if ($json === false) throw new InvalidArgumentException('Unable to read dashboard template.');
        $dashboard = $this->renderer->decodeJsonPreservingEmptyObjects($json, $path);

        $dashboard = $this->removeChildOrgFilters($dashboard);
        $widgetMap = $this->buildWidgetMap($dashboard);
        $effectiveCodes = array_keys($effective);

        $groups = [];
        foreach ($dashboard['widgets'] ?? [] as $group) {
            if (($group['definition']['type'] ?? null) !== 'group') continue;
            $title = $group['definition']['title'] ?? '';
            if ($title === 'Estimated Usage Summary') {
                $groups['summary'] = $group;
            } else {
                foreach (config('datadog.groups', []) as $key => $groupTitle) {
                    if ($title === $groupTitle) $groups[$key] = $group;
                }
            }
        }

        $summaryWidgets = [];
        $categoryWidgets = [];

        foreach ($effective as $code => $commitment) {
            $definition = $licenses[$code];
            $raw = $commitment * (float) ($definition['multiplier'] ?? 1);
            $existingSummary = $widgetMap[$code]['summary'] ?? [];
            $existingBreakdown = $widgetMap[$code]['breakdown'] ?? [];

            $summary = $this->findWidgetsByType($existingSummary, 'query_value');
            if ($summary === []) {
                $summary = [$this->makeQueryValueWidget($definition, $code, $raw)];
            }
            foreach ($summary as $widget) {
                $summaryWidgets[] = $this->prepareWidget($widget, $definition, $code, $raw, 'summary');
            }

            $breakdown = $existingBreakdown;
            if ($breakdown === []) {
                $breakdown = [
                    $this->makeQueryValueWidget($definition, $code, $raw),
                    $this->makeTimeseriesWidget($definition, $code, $raw),
                ];
                if ($code === 'infra_host') {
                    $breakdown = [
                        $this->makeQueryValueWidget($definition, $code, $raw),
                        $this->makeToplistWidget($definition, $code, $raw),
                        $this->makeTimeseriesWidget($definition, $code, $raw),
                    ];
                }
            } elseif ($code === 'infra_host' && count($breakdown) === 2) {
                $hasToplist = collect($breakdown)->contains(fn ($widget) => ($widget['definition']['type'] ?? '') === 'toplist');
                if (!$hasToplist) {
                    $breakdown[] = $this->makeToplistWidget($definition, $code, $raw);
                }
            } elseif (count($breakdown) === 1 && ($breakdown[0]['definition']['type'] ?? '') === 'query_value') {
                $breakdown[] = $this->makeTimeseriesWidget($definition, $code, $raw);
            }

            foreach ($breakdown as $widget) {
                $categoryWidgets[$definition['group']][] = $this->prepareWidget($widget, $definition, $code, $raw, 'breakdown');
            }
        }

        $newGroups = [];
        if ($summaryWidgets !== []) {
            $summaryGroup = $groups['summary'] ?? $this->makeGroup('Estimated Usage Summary');
            $summaryGroup['definition']['widgets'] = $this->reflowWidgets($this->dedupeWidgets($summaryWidgets));
            $summaryGroup['layout'] = $this->resizeGroupLayout($summaryGroup['layout'] ?? [], $summaryGroup['definition']['widgets']);
            $newGroups[] = $summaryGroup;
        }

        foreach (config('datadog.groups', []) as $key => $title) {
            if (empty($categoryWidgets[$key])) continue;
            $group = $groups[$key] ?? $this->makeGroup($title);
            $group['definition']['title'] = $title;
            $group['definition']['widgets'] = $this->reflowWidgets($this->dedupeWidgets($categoryWidgets[$key]));
            $group['layout'] = $this->resizeGroupLayout($group['layout'] ?? [], $group['definition']['widgets']);
            $newGroups[] = $group;
        }

        $dashboard['widgets'] = $this->reflowGroups($newGroups);
        return $dashboard;
    }

    private function buildWidgetMap(array $dashboard): array
    {
        $licenses = config('datadog.licenses', []);
        $idToCode = [];
        foreach ($licenses as $code => $definition) {
            foreach ($definition['dashboard_widget_ids'] ?? [] as $id) $idToCode[(int) $id] = $code;
        }

        $map = [];
        foreach ($dashboard['widgets'] ?? [] as $group) {
            $groupTitle = $group['definition']['title'] ?? '';
            $isSummary = $groupTitle === 'Estimated Usage Summary';
            foreach ($group['definition']['widgets'] ?? [] as $widget) {
                $id = isset($widget['id']) ? (int) $widget['id'] : null;
                if ($id === null || $id === self::DASHBOARD_NOTE_ID || !isset($idToCode[$id])) continue;
                $code = $idToCode[$id];
                $map[$code] ??= ['summary' => [], 'breakdown' => []];
                if ($isSummary) $map[$code]['summary'][] = $widget;
                else $map[$code]['breakdown'][] = $widget;
            }
        }
        return $map;
    }

    private function prepareWidget(array $widget, array $definition, string $code, float $raw, string $section = 'breakdown'): array
    {
        $widget['id'] = $widget['id'] ?? $this->generatedWidgetId($code, $definition['name'], $widget['definition']['type'] ?? 'widget');
        $widget['definition'] = $this->replaceQueriesAndSettings($widget['definition'] ?? [], $definition);
        $widget['definition'] = $this->applyLicenseThreshold($widget['definition'], $raw);
        $widget['layout'] = $this->applyWidgetLayout($widget['layout'] ?? [], $code, $widget['definition']['type'] ?? 'widget', $section);

        if (($widget['definition']['type'] ?? '') === 'timeseries') {
            $widget['definition']['title'] = $this->timeseriesTitle($definition['name']);
            $widget['definition']['markers'] = [[
                'label' => 'y = '.$this->formatNumber($raw),
                'value' => 'y = '.$this->formatNumber($raw),
                'display_type' => 'error bold',
            ]];
        }

        return $widget;
    }

    private function applyWidgetLayout(array $layout, string $code, string $type, string $section): array
    {
        if ($section === 'summary') {
            return ['x' => 0, 'y' => 0, 'width' => 2, 'height' => 2];
        }

        if ($code === 'infra_host') {
            if ($type === 'timeseries') {
                return ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 2];
            }
            return ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2];
        }

        if ($type === 'timeseries') {
            return ['x' => 0, 'y' => 0, 'width' => 9, 'height' => 2];
        }

        return ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2];
    }

    private function replaceQueriesAndSettings(array $definition, array $license): array
    {
        $query = $license['query'];
        $aggregator = $license['aggregator'];
        $time = $license['time'] ?? null;

        if (isset($definition['requests']) && is_array($definition['requests'])) {
            foreach ($definition['requests'] as &$request) {
                if (isset($request['queries']) && is_array($request['queries'])) {
                    foreach ($request['queries'] as &$q) {
                        $q['query'] = $query;
                        $q['aggregator'] = $aggregator;
                    }
                    unset($q);
                }
            }
            unset($request);
        }

        if ($time !== null) $definition['time'] = $time;
        return $definition;
    }

    private function applyLicenseThreshold(array $definition, float $raw): array
    {
        if (isset($definition['requests']) && is_array($definition['requests'])) {
            foreach ($definition['requests'] as &$request) {
                if (!isset($request['conditional_formats']) || !is_array($request['conditional_formats'])) continue;
                $formats = [];
                foreach ($request['conditional_formats'] as $format) {
                    $palette = $format['palette'] ?? null;
                    if (!in_array($palette, ['white_on_green', 'white_on_red'], true)) continue;
                    $format['value'] = fmod($raw, 1.0) === 0.0 ? (int) $raw : round($raw, 4);
                    $format['comparator'] = $palette === 'white_on_green' ? '<=' : '>=';
                    $formats[] = $format;
                }
                if ($formats !== []) $request['conditional_formats'] = $formats;
            }
            unset($request);
        }
        return $definition;
    }

    private function makeQueryValueWidget(array $license, string $code, float $raw): array
    {
        $unit = $license['input_unit'];
        return [
            'id' => $this->generatedWidgetId($code, $license['name'], 'query_value'),
            'layout' => ['x' => 0, 'y' => 0, 'width' => 2, 'height' => 2],
            'definition' => [
                'title' => 'Estimated '.$license['name'].' Count',
                'type' => 'query_value',
                'requests' => [[
                    'formulas' => [[
                        'number_format' => ['unit' => ['type' => 'custom_unit_label', 'label' => $unit]],
                        'formula' => 'query1',
                    ]],
                    'response_format' => 'scalar',
                    'queries' => [[
                        'query' => $license['query'], 'data_source' => 'metrics', 'name' => 'query1', 'aggregator' => $license['aggregator'],
                    ]],
                    'conditional_formats' => [
                        ['comparator' => '<=', 'value' => $raw, 'palette' => 'white_on_green'],
                        ['comparator' => '>=', 'value' => $raw, 'palette' => 'white_on_red'],
                    ],
                ]],
                'autoscale' => true,
                'text_align' => 'center',
                'precision' => 2,
                'timeseries_background' => ['yaxis' => new \stdClass(), 'type' => 'bars'],
            ],
        ];
    }

    private function makeToplistWidget(array $license, string $code, float $raw): array
    {
        return [
            'id' => $this->generatedWidgetId($code, $license['name'], 'toplist'),
            'layout' => ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2],
            'definition' => [
                'title' => 'Estimated '.$license['name'].' Count',
                'type' => 'toplist',
                'requests' => [[
                    'formulas' => [['formula' => 'query1']],
                    'queries' => [[
                        'query' => $license['query'], 'data_source' => 'metrics', 'name' => 'query1', 'aggregator' => $license['aggregator'],
                    ]],
                    'response_format' => 'scalar',
                ]],
                'conditional_formats' => [
                    ['comparator' => '<=', 'value' => $raw, 'palette' => 'white_on_green'],
                    ['comparator' => '>=', 'value' => $raw, 'palette' => 'white_on_red'],
                ],
            ],
        ];
    }

    private function makeTimeseriesWidget(array $license, string $code, float $raw): array
    {
        $query = $license['query'];
        $return = [
            'id' => $this->generatedWidgetId($code, $license['name'], 'timeseries'),
            'layout' => ['x' => 0, 'y' => 0, 'width' => 9, 'height' => 2],
            'definition' => [
                'title' => $this->timeseriesTitle($license['name']),
                'show_legend' => false,
                'type' => 'timeseries',
                'requests' => [[
                    'formulas' => [['formula' => 'query1']],
                    'response_format' => 'timeseries',
                    'queries' => [[
                        'query' => $query, 'data_source' => 'metrics', 'name' => 'query1', 'aggregator' => $license['aggregator'],
                    ]],
                    'style' => ['palette' => 'red', 'line_type' => 'solid', 'line_width' => 'normal'],
                    'display_type' => 'line',
                ], [
                    'formulas' => [[ 'formula' => "calendar_shift(query1, '-4w', 'UTC')" ]],
                    'response_format' => 'timeseries',
                    'queries' => [[
                        'query' => $query, 'data_source' => 'metrics', 'name' => 'query1', 'aggregator' => $license['aggregator'],
                    ]],
                    'style' => ['palette' => 'green', 'line_type' => 'dashed', 'line_width' => 'normal'],
                    'display_type' => 'line',
                ]],
                'yaxis' => ['include_zero' => true, 'scale' => 'linear', 'label' => '', 'min' => 'auto', 'max' => 'auto'],
                'markers' => [[
                    'label' => 'y = '.$this->formatNumber($raw), 'value' => 'y = '.$this->formatNumber($raw), 'display_type' => 'error bold',
                ]],
            ],
        ];
        if (isset($license['time'])) $return['definition']['time'] = $license['time'];
        return $return;
    }

    private function makeGroup(string $title): array
    {
        return [
            'definition' => ['type' => 'group', 'layout_type' => 'ordered', 'title' => $title, 'widgets' => []],
            'layout' => ['x' => 0, 'y' => 0, 'width' => 12, 'height' => 1],
        ];
    }

    private function findWidgetsByType(array $widgets, string $type): array
    {
        return array_values(array_filter($widgets, fn ($widget) => ($widget['definition']['type'] ?? null) === $type));
    }

    private function dedupeWidgets(array $widgets): array
    {
        $seen = [];
        $result = [];
        foreach ($widgets as $widget) {
            $id = $widget['id'] ?? null;
            if ($id !== null && isset($seen[$id])) continue;
            if ($id !== null) $seen[$id] = true;
            $result[] = $widget;
        }
        return $result;
    }

    private function removeChildOrgFilters(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) $value[$k] = $this->removeChildOrgFilters($v);
            return $value;
        }
        if (is_string($value)) {
            $value = preg_replace('/(^|\{)child_org_name:[^,}]+,?/', '$1', $value);
            $value = preg_replace('/,\s*}/', '}', $value);
            $value = str_replace(['{,', '{ }'], ['{', '{}'], $value);
        }
        return $value;
    }

    private function reflowWidgets(array $widgets, int $gridWidth = 12): array
    {
        $cursorX = 0; $cursorY = 0; $rowHeight = 0;
        foreach ($widgets as &$widget) {
            $width = max(1, min($gridWidth, (int) ($widget['layout']['width'] ?? 4)));
            $height = max(1, (int) ($widget['layout']['height'] ?? 3));
            if ($cursorX > 0 && $cursorX + $width > $gridWidth) { $cursorX = 0; $cursorY += $rowHeight; $rowHeight = 0; }
            $widget['layout']['x'] = $cursorX; $widget['layout']['y'] = $cursorY; $widget['layout']['width'] = $width; $widget['layout']['height'] = $height;
            $cursorX += $width; $rowHeight = max($rowHeight, $height);
            if ($cursorX >= $gridWidth) { $cursorX = 0; $cursorY += $rowHeight; $rowHeight = 0; }
        }
        unset($widget);
        return $widgets;
    }

    private function reflowGroups(array $groups): array
    {
        $cursorY = 0;
        foreach ($groups as &$group) {
            $group['layout']['x'] = 0; $group['layout']['y'] = $cursorY;
            $group['layout']['height'] = max(1, (int) ($group['layout']['height'] ?? 1));
            $cursorY += $group['layout']['height'];
        }
        unset($group);
        return $groups;
    }

    private function resizeGroupLayout(array $layout, array $widgets): array
    {
        $maxBottom = 1;
        foreach ($widgets as $widget) $maxBottom = max($maxBottom, (int) ($widget['layout']['y'] ?? 0) + (int) ($widget['layout']['height'] ?? 1));
        $layout['x'] = 0; $layout['width'] = 12; $layout['height'] = $maxBottom;
        return $layout;
    }

    private function timeseriesTitle(string $name): string
    {
        return 'Current Month Estimated '.$name.' vs. Prior Month Count';
    }

    private function generatedWidgetId(string $code, string $name, string $type): int
    {
        return abs(crc32($code.'|'.$name.'|'.$type)) * 1000 + strlen($name);
    }

    private function isDerivedLicense(string $code, array $input): bool
    {
        return in_array($code, ['container', 'custom_metrics', 'custom_events', 'apm_ingested_spans', 'apm_indexed_spans', 'apm_profiler_host', 'apm_profiler_container'], true);
    }

    private function monitorFilename(string $name): string
    {
        $filename = preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?: 'license';
        return strtolower(trim($filename, '-')).'-monitor.json';
    }

    private function formatNumber(float $value): string
    {
        if (fmod($value, 1.0) === 0.0) return number_format($value, 0, '.', '');
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }
}
