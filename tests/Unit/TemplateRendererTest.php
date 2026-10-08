<?php

namespace Tests\Unit;

use App\Services\TemplateRenderer;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    public function test_json_template_placeholders_are_rendered(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'dd-template-');
        file_put_contents($path, '{"name":"{{name}}","threshold":{{threshold}}}');

        $result = (new TemplateRenderer())->renderJsonTemplate($path, [
            'name' => 'APM Host',
            'threshold' => 90,
        ]);

        unlink($path);

        $this->assertSame('APM Host', $result['name']);
        $this->assertSame(90, $result['threshold']);
    }

    public function test_empty_json_objects_remain_objects(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'dd-template-');
        file_put_contents($path, '{"definition":{"type":"query_value","timeseries_background":{"yaxis":{},"type":"bars"}}}');

        $result = (new TemplateRenderer())->renderJsonTemplate($path, []);

        unlink($path);

        $json = json_encode($result, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('"yaxis":{}', $json);
    }
}
