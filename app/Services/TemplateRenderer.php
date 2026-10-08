<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class TemplateRenderer
{
    /**
     * Render a JSON template by replacing {{key}} placeholders recursively.
     * Values are escaped at the JSON level, not at the HTML level.
     */
    public function renderJsonTemplate(string $path, array $variables): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("Template not found: {$path}");
        }

        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException("Unable to read template: {$path}");
        }

        foreach ($variables as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            // Replace a quoted placeholder with a JSON-encoded value so that
            // strings, numbers, booleans, nulls, arrays, and objects retain
            // their correct JSON types.
            $json = str_replace('"'.$placeholder.'"', $encoded, $json);
            $json = str_replace($placeholder, (string) $value, $json);
        }

        return $this->decodeJsonPreservingEmptyObjects($json, $path);
    }

    /**
     * Decode JSON for application manipulation while preserving empty JSON
     * objects as stdClass instances. PHP's json_decode(..., true) converts
     * an empty object {} into [], which produces invalid Datadog dashboard
     * payloads for fields such as timeseries_background.yaxis.
     */
    public function decodeJsonPreservingEmptyObjects(string $json, string $source = 'JSON'): array
    {
        try {
            $decoded = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException("Invalid JSON in {$source}: {$e->getMessage()}", 0, $e);
        }

        $normalized = $this->normalizeJsonValue($decoded);

        if (!is_array($normalized)) {
            throw new RuntimeException("Rendered JSON root must be an object for {$source}.");
        }

        return $normalized;
    }

    private function normalizeJsonValue(mixed $value): mixed
    {
        if ($value instanceof \stdClass) {
            // Preserve {} as an object. Non-empty JSON objects become PHP
            // arrays so the rest of the generator can access them naturally.
            $properties = get_object_vars($value);
            if ($properties === []) {
                return new \stdClass();
            }

            $result = [];
            foreach ($properties as $key => $item) {
                $result[$key] = $this->normalizeJsonValue($item);
            }
            return $result;
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->normalizeJsonValue($item);
            }
            return $result;
        }

        return $value;
    }
}
