<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Bin;

class SettingsController extends Controller
{
    /**
     * Show settings page (only for waste_levels management).
     */
    public function index()
    {
        // Load bins for selection
        $bins = Bin::select('id', 'type')->orderBy('type')->get();

        return view('admin.settings', compact('bins'));
    }

    /**
     * AJAX: return waste_levels rows for a selected bin and optional date_from.
     * Request: bin_id (required), date_from (optional), limit (optional)
     */
    public function loadTable(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bin_id' => 'required|integer|exists:bins,id',
            'date_from' => 'nullable|date',
            'limit' => 'nullable|integer|min:1|max:1000'
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $binId = $request->input('bin_id');
        $limit = $request->input('limit', 200);

        $query = DB::table('waste_levels')->where('bin_id', $binId);

        if ($request->filled('date_from')) {
            try {
                $date = Carbon::parse($request->date_from)->startOfDay();
                $query->where('created_at', '>=', $date);
            } catch (\Exception $e) {
                // ignore (validator should have caught)
            }
        }

        $rows = $query->orderBy('created_at', 'desc')->limit($limit)->get();

        // Columns for front-end rendering
        $columns = $rows->isNotEmpty() ? array_keys((array) $rows->first()) : ['id','bin_id','weight','level','created_at'];

        return response()->json([
            'success' => true,
            'columns' => $columns,
            'rows' => $rows,
            'count' => $rows->count()
        ]);
    }

    /**
     * Delete waste_levels rows matching filters.
     * Form POST expects: bin_id (required), date_from (nullable), confirmation (required === 'DELETE')
     */
    public function deleteRecords(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bin_id' => 'required|integer|exists:bins,id',
            'date_from' => 'nullable|date',
            'confirmation' => 'required|string'
        ]);

        if ($v->fails()) {
            return redirect()->back()->withErrors($v)->withInput();
        }

        // require exact confirmation word
        if ($request->input('confirmation') !== 'DELETE') {
            return redirect()->back()->with('error', 'You must type the confirmation word: DELETE');
        }

        $binId = $request->input('bin_id');

        $query = DB::table('waste_levels')->where('bin_id', $binId);

        if ($request->filled('date_from')) {
            try {
                $date = Carbon::parse($request->date_from)->startOfDay();
                $query->where('created_at', '>=', $date);
            } catch (\Exception $e) {
                // ignore
            }
        }

        $count = $query->count();

        if ($count === 0) {
            return redirect()->back()->with('info', 'No records matched the selected filters.');
        }

        $query->delete();

        return redirect()->back()->with('success', "Deleted {$count} record(s) from waste_levels for the selected filter.");
    }
}
