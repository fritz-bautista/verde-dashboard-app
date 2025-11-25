<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WasteLevel;
use Illuminate\Support\Facades\DB;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;

class ReportController extends Controller
{
    public function index()
    {
        $binData = WasteLevel::select('bins.type as bin', DB::raw('SUM(waste_levels.weight) as total_weight'))
            ->join('bins', 'waste_levels.bin_id', '=', 'bins.id')
            ->groupBy('bins.type')
            ->orderByDesc('total_weight')
            ->limit(5)
            ->get();

        $floorData = WasteLevel::select('levels.name as floor', DB::raw('SUM(waste_levels.weight) as total_weight'))
            ->join('bins', 'waste_levels.bin_id', '=', 'bins.id')
            ->join('levels', 'bins.level_id', '=', 'levels.id')
            ->groupBy('levels.name')
            ->orderByDesc('total_weight')
            ->limit(5)
            ->get();

        $recycleData = \DB::table('colleges')
            ->join('levels', 'colleges.floor', '=', 'levels.id')
            ->join('bins', 'bins.level_id', '=', 'levels.id')
            ->join('waste_levels', 'waste_levels.bin_id', '=', 'bins.id')
            ->select(
                'colleges.name as college_name',
                \DB::raw('SUM(waste_levels.weight) as total_weight')
            )
            ->whereIn('bins.type', [
                'Recyclable - Bottled Water',
                'Recyclable - Paper'
            ])
            ->groupBy('colleges.id', 'colleges.name')
            ->orderByDesc('total_weight')
            ->get();


        return view('main.reports', compact('recycleData', 'floorData', 'binData'));
    }

    // Download handler for PDF or Excel
    public function download($type, $format, Request $request)
    {
        $data = [];
        $title = '';

        if ($type === 'college') {
            $title = 'College Report';
            $data = WasteLevel::select('bins.type as type', DB::raw('SUM(waste_levels.weight) as total_weight'))
                ->join('bins', 'waste_levels.bin_id', '=', 'bins.id')
                ->groupBy('bins.type')
                ->get();
        } elseif ($type === 'floor') {
            $title = 'Floor Report';
            $data = WasteLevel::select('levels.name as floor', DB::raw('SUM(waste_levels.weight) as total_weight'))
                ->join('bins', 'waste_levels.bin_id', '=', 'bins.id')
                ->join('levels', 'bins.level_id', '=', 'levels.id')
                ->groupBy('levels.name')
                ->get();
        } elseif ($type === 'bin') {
            $title = 'Bin Report';
            $data = WasteLevel::select('bins.type as bin', DB::raw('SUM(waste_levels.weight) as total_weight'))
                ->join('bins', 'waste_levels.bin_id', '=', 'bins.id')
                ->groupBy('bins.type')
                ->get();
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView('reports.export', compact('data', 'title', 'type'));
            return $pdf->download(strtolower($title) . '.pdf');
        }

        if ($format === 'excel') {
            return Excel::download(new GenericExport($data), strtolower($title) . '.xlsx');
        }

        return back()->with('error', 'Invalid format.');
    }
}
