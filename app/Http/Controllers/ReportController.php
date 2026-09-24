<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(ReportService $reportService): View
    {
        $stats = $reportService->getUserStats(auth()->user());

        return view('reports.index', compact('stats'));
    }
}
