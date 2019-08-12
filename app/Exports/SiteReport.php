<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SiteReport implements FromView
{
    private $reportData;

    public function __construct($reportData)
    {       
        // dd($reportData);
        $this->reportData = $reportData;
    }

    public function view(): View
    {
        return view('exportData', [
            'reportData' => $this->reportData
        ]);
    }
}
