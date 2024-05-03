<?php

namespace App\Jobs;

use App\Models\ApplicationsAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplicationsAnalysisJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ApplicationsAnalysis $applicationsAnalysis;

    /**
     * Create a new job instance.
     */
    public function __construct(ApplicationsAnalysis $applicationsAnalysis)
    {
        $this->applicationsAnalysis = $applicationsAnalysis;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->applicationsAnalysis->started_at = now();
        $this->applicationsAnalysis->status = 'Rodando...';
        $this->applicationsAnalysis->save(); // Save log in real-time
        // Command to start Docker container
        $dockerCommand = "docker run -it -v /var/www/html/storage/app/public:/home/node/app/result --rm analyzeragent node index.js {$this->applicationsAnalysis->application->url}";

        // Open a pipe to the Docker process
        $process = proc_open($dockerCommand, [1 => ['pipe', 'w']], $pipes);

        if (is_resource($process)) {
            // Read output from Docker container line by line
            while (!feof($pipes[1])) {
                $line = fgets($pipes[1]);
                // Append each line to the log column
                $this->applicationsAnalysis->log .= $line;
                $this->applicationsAnalysis->save(); // Save log in real-time
            }

            // Close the process and pipes
            fclose($pipes[1]);
            proc_close($process);

            $this->applicationsAnalysis->finish_at = now();
            $this->applicationsAnalysis->status = 'Finalizada.';
            $this->applicationsAnalysis->save(); // Save log in real-time
        }
    }
}
