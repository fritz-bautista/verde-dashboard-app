<?php

namespace App\Http\Controllers;

use App\Models\WasteLevel;
use App\Models\Bin;

class NotificationController extends Controller
{
    public function fetch()
    {
        $threshold = 80;

        $latest = WasteLevel::select('bin_id', 'weight', 'level', 'created_at')
            ->latest('created_at')
            ->get()
            ->groupBy('bin_id')
            ->map(fn($g) => $g->first());
        $notifications = [];
        foreach ($latest as $row) {
            if ($row->level >= $threshold) {
                $bin = Bin::find($row->bin_id);

                $notifications[] = [
                    'title' => 'Bin Full Alert',
                    'message' => $bin->name . ' is now ' . $row->level . '% full.',
                    'time' => $row->created_at->diffForHumans(),
                ];
            }
        }

        return response()->json($notifications);
    }
}
