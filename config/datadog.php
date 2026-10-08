<?php

return [
    'name' => 'Datadog License Monitoring Generator',

    'groups' => [
        'infrastructure' => 'Infrastructure License Usage',
        'apm' => 'APM License Usage',
        'dbm' => 'DBM License Usage',
        'digital_experience' => 'Digital Experience License Usage',
        'synthetic' => 'Synthetic License Usage',
        'logs' => 'Logs Management License Usage',
    ],

    'licenses' => [
        'infra_host' => [
            'name' => 'Infra Host', 'group' => 'infrastructure', 'input_unit' => 'Host', 'description' => 'Infrastructure host entitlement.',
            'query' => 'sum:datadog.estimated_usage.hosts{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.hosts{*}',
            'dashboard_widget_ids' => [8000354488851480, 8201407916616365, 3943915106225993, 3417238087874396],
        ],
        'container' => [
            'name' => 'Container', 'group' => 'infrastructure', 'input_unit' => 'Container', 'description' => 'Container entitlement derived from Infra Host plan plus optional add-on.',
            'query' => 'sum:datadog.estimated_usage.containers{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.containers{*}',
            'dashboard_widget_ids' => [6733428502258016, 311061126591869],
        ],
        'custom_metrics' => [
            'name' => 'Custom Metrics', 'group' => 'infrastructure', 'input_unit' => 'Metric', 'description' => 'Custom Metrics entitlement derived from Infra Host plan.',
            'query' => 'sum:datadog.estimated_usage.metrics.custom{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.metrics.custom{*}',
            'dashboard_widget_ids' => [],
        ],
        'custom_events' => [
            'name' => 'Custom Events', 'group' => 'infrastructure', 'input_unit' => 'Event', 'description' => 'Custom Events entitlement derived from Infra Host plan.',
            'query' => 'sum:datadog.estimated_usage.events.custom_events{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.events.custom_events{*}',
            'dashboard_widget_ids' => [],
        ],
        'ndm' => [
            'name' => 'NDM', 'group' => 'infrastructure', 'input_unit' => 'Device', 'description' => 'Network Device Monitoring entitlement.',
            'query' => 'sum:datadog.estimated_usage.network.devices{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.network.devices{*}',
            'dashboard_widget_ids' => [],
        ],
        'cnm' => [
            'name' => 'CNM', 'group' => 'infrastructure', 'input_unit' => 'Host', 'description' => 'Cloud Network Monitoring entitlement.',
            'query' => 'sum:datadog.estimated_usage.network.hosts{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.network.hosts{*}',
            'dashboard_widget_ids' => [],
        ],

        'apm_host' => [
            'name' => 'APM Host', 'group' => 'apm', 'input_unit' => 'Host', 'description' => 'APM host entitlement and the basis for APM plan allowances.',
            'query' => 'sum:datadog.estimated_usage.apm_hosts{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.apm_hosts{*}',
            'dashboard_widget_ids' => [4103348238029368, 4577650088632237, 5829056244105866],
        ],
        'apm_profiler_host' => [
            'name' => 'APM Profiler Host', 'group' => 'apm', 'input_unit' => 'Host', 'description' => 'Enterprise allowance: 1 profiler host per APM host.',
            'query' => 'sum:datadog.estimated_usage.profiling.hosts{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.profiling.hosts{*}',
            'dashboard_widget_ids' => [681417857388731, 5122212910656666, 5985020923344648],
        ],
        'apm_profiler_container' => [
            'name' => 'APM Profiler Container', 'group' => 'apm', 'input_unit' => 'Container', 'description' => 'Enterprise allowance: 4 profiler containers per APM host.',
            'query' => 'sum:datadog.estimated_usage.profiling.containers{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.profiling.containers{*}',
            'dashboard_widget_ids' => [7989952222675403, 801759330552124, 5049532764061877],
        ],
        'apm_ingested_spans' => [
            'name' => 'APM Ingested Spans', 'group' => 'apm', 'input_unit' => 'GB', 'multiplier' => 1000000000,
            'description' => 'APM ingested span allowance in GB.',
            'query' => 'sum:datadog.estimated_usage.apm.ingested_bytes{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.apm.ingested_bytes{*}.as_count()', 'dashboard_widget_ids' => [6442265749133580, 3328305434510156, 1390116612704601],
        ],
        'apm_indexed_spans' => [
            'name' => 'APM Indexed Spans', 'group' => 'apm', 'input_unit' => 'Million', 'multiplier' => 1000000,
            'description' => 'APM indexed span allowance in millions.',
            'query' => 'sum:datadog.estimated_usage.apm.indexed_spans{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.apm.indexed_spans{*}.as_count()', 'dashboard_widget_ids' => [7732885365166016, 959722615220571, 8897852954150384],
        ],

        'dbm_host' => [
            'name' => 'DBM Host', 'group' => 'dbm', 'input_unit' => 'Host', 'description' => 'Database Monitoring host entitlement.',
            'query' => 'sum:datadog.estimated_usage.dbm.hosts{*}', 'aggregator' => 'last', 'monitor_query' => 'avg(last_5m):sum:datadog.estimated_usage.dbm.hosts{*}',
            'dashboard_widget_ids' => [3302563057718042, 3838903302825319],
        ],

        'rum_sessions' => [
            'name' => 'RUM Session', 'group' => 'digital_experience', 'input_unit' => 'Session', 'description' => 'RUM Session plan.',
            'query' => 'sum:datadog.estimated_usage.rum.sessions{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.rum.sessions{*}.as_count()', 'dashboard_widget_ids' => [4025355547279158, 211260383580661, 3668985643717780],
        ],
        'rum_investigate' => [
            'name' => 'RUM Investigate', 'group' => 'digital_experience', 'input_unit' => 'Session', 'description' => 'RUM Investigate allowance for RUM Without Limit.',
            'query' => 'sum:datadog.estimated_usage.rum.indexed_sessions{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.rum.indexed_sessions{*}.as_count()', 'dashboard_widget_ids' => [],
        ],
        'rum_measure' => [
            'name' => 'RUM Measure', 'group' => 'digital_experience', 'input_unit' => 'Session', 'description' => 'RUM Measure allowance for RUM Without Limit.',
            'query' => 'sum:datadog.estimated_usage.rum.ingested_sessions{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.rum.ingested_sessions{*}.as_count()', 'dashboard_widget_ids' => [],
        ],
        'rum_session_replay' => [
            'name' => 'RUM Session Replay', 'group' => 'digital_experience', 'input_unit' => 'Session', 'description' => 'RUM Session Replay allowance.',
            'query' => 'sum:datadog.estimated_usage.rum.sessions{sku:replay}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.rum.sessions{sku:replay}.as_count()', 'dashboard_widget_ids' => [7376741881431393, 8004941990559705, 553687546777932],
        ],

        'synthetic_api' => [
            'name' => 'API Test', 'group' => 'synthetic', 'input_unit' => 'Runs', 'description' => 'Synthetic API Test runs per month.',
            'query' => 'sum:datadog.estimated_usage.synthetics.api_test_runs{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.synthetics.api_test_runs{*}.as_count()', 'dashboard_widget_ids' => [],
        ],
        'synthetic_browser' => [
            'name' => 'Browser Test', 'group' => 'synthetic', 'input_unit' => 'Runs', 'description' => 'Synthetic Browser Test runs per month.',
            'query' => 'sum:datadog.estimated_usage.synthetics.browser_test_runs{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.synthetics.browser_test_runs{*}.as_count()', 'dashboard_widget_ids' => [],
        ],

        'logs_ingested' => [
            'name' => 'Logs Ingested', 'group' => 'logs', 'input_unit' => 'GB', 'multiplier' => 1000000000, 'description' => 'Logs ingested allowance in GB per month.',
            'query' => 'sum:datadog.estimated_usage.logs.ingested_bytes{*}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.logs.ingested_bytes{*}.as_count()', 'dashboard_widget_ids' => [],
        ],
        'logs_indexed' => [
            'name' => 'Logs Indexed', 'group' => 'logs', 'input_unit' => 'Events', 'description' => 'Logs indexed allowance per month.',
            'query' => 'sum:datadog.estimated_usage.logs.ingested_events{datadog_index:*,datadog_is_excluded:false}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.logs.ingested_events{datadog_index:*,datadog_is_excluded:false}.as_count()', 'dashboard_widget_ids' => [],
        ],
        'cloud_siem' => [
            'name' => 'Cloud SIEM', 'group' => 'logs', 'input_unit' => 'Events', 'description' => 'Cloud SIEM indexed event allowance per month.',
            'query' => 'sum:datadog.estimated_usage.logs.ingested_events{datadog_index:cloud-siem*,datadog_is_excluded:false}.as_count()', 'aggregator' => 'sum', 'time' => ['type' => 'monthly', 'offset' => 0],
            'monitor_query' => 'sum(current_1mo):sum:datadog.estimated_usage.logs.ingested_events{datadog_index:cloud-siem*,datadog_is_excluded:false}.as_count()', 'dashboard_widget_ids' => [],
        ],
    ],
];
