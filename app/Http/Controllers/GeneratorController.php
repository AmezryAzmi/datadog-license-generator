<?php

namespace App\Http\Controllers;

use App\Services\DatadogLicenseGenerator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ZipArchive;
use InvalidArgumentException;

class GeneratorController extends Controller
{
    public function index(): View
    {
        return view('generator', [
            'licenses' => config('datadog.licenses', []),
            'defaults' => config('datadog.defaults', []),
        ]);
    }

    public function generate(Request $request, DatadogLicenseGenerator $generator): View
    {
        try {
            $result = $generator->generate($request->all());
        } catch (InvalidArgumentException $exception) {
            $errors = json_decode($exception->getMessage(), true);
            return view('generator', [
                'licenses' => config('datadog.licenses', []),
                'defaults' => config('datadog.defaults', []),
                'generationErrors' => collect($errors ?? [])->flatten()->values()->all(),
                'old' => $request->all(),
            ]);
        }

        return view('result', [
            'result' => $result,
        ]);
    }

    public function downloadMonitors(Request $request)
    {
        $monitors = $request->input('monitors', []);
        if (is_string($monitors)) {
            $monitors = json_decode($monitors, true);
        }
        if (!is_array($monitors) || $monitors === []) {
            abort(422, 'No monitor JSON was supplied.');
        }

        $path = tempnam(sys_get_temp_dir(), 'dd-license-monitors-');
        $zip = new ZipArchive();
        if ($path === false || $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create monitor ZIP.');
        }

        foreach ($monitors as $monitor) {
            if (!is_array($monitor) || empty($monitor['filename']) || !isset($monitor['json'])) {
                continue;
            }
            $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $monitor['filename']);
            $zip->addFromString($filename, (string) $monitor['json']);
        }
        $zip->close();

        return response()->download($path, 'datadog-license-monitors.zip')->deleteFileAfterSend(true);
    }

}
