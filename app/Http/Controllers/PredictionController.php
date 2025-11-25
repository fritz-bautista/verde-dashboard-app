<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PredictionController extends Controller
{
    public function run(Request $request)
    {
        $bin_id = $request->input('bin_id', 1); // default bin if not provided

        $response = Http::post(env('ROUTE_PREDICTION_API') . '/predict', [
            'bin_id' => $bin_id,
            'days_ahead' => 7,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $predictions = $data['predictions'] ?? []; // fallback empty array
            $error = $data['error'] ?? null;

            if ($error) {
                session()->flash('error', 'Prediction API Error: ' . $error);
            }

            return view('main.dashboard', compact('predictions'));
        } else {
            return redirect()->back()->with('error', 'Prediction failed: ' . $response->body());
        }

    }
}
