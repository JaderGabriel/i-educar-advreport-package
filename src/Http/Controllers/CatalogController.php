<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use iEducar\Packages\AdvancedReports\Support\ReportsCatalog;
use Illuminate\View\View;

class CatalogController
{
    public function index(): View
    {
        return view('advanced-reports::catalog.index', [
            'grupos' => ReportsCatalog::agrupados(),
            'formatosEdital' => ReportsCatalog::formatosEdital(),
            'versao' => ReportsCatalog::versaoPacote(),
        ]);
    }
}
