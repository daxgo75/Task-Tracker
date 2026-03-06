<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display Swagger UI documentation
     */
    public function index(): View
    {
        $documentation = config('l5-swagger.default');
        $documentationTitle = config("l5-swagger.documentations.{$documentation}.api.title", 'L5 Swagger UI');
        $urlsToDocs = [
            $documentationTitle => url('/api/docs.json'),
        ];

        return view('vendor.l5-swagger.index', [
            'documentation' => $documentation,
            'documentationTitle' => $documentationTitle,
            'urlsToDocs' => $urlsToDocs,
            'useAbsolutePath' => config('l5-swagger.documentations.default.paths.use_absolute_path', true),
            'operationsSorter' => config('l5-swagger.defaults.ui.operations_sorter'),
            'configUrl' => null,
            'validatorUrl' => null,
        ]);
    }
}
