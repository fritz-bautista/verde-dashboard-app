<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PredictWasteOverflow extends Command
{
    protected $signature = 'predict:waste';
    protected $description = 'Run Python script to predict waste overflow';

    public function handle()
    {
        $process = new Process([
            base_path('ml/venv/Scripts/python.exe'),
            base_path('ml/predict_overflow.py')
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->info("✅ Predictions completed:\n" . $process->getOutput());
    }

}
