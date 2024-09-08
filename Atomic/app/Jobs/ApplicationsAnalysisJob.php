<?php

namespace App\Jobs;

use App\Models\Applications;
use App\Models\ApplicationsAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ApplicationsAnalysisJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ApplicationsAnalysis $applicationsAnalysis;
    // Defina o tempo limite do trabalho (em segundos)
    public $timeout = 3600; // 1 hora

    /**
     * Create a new job instance.
     */
    public function __construct(ApplicationsAnalysis $applicationsAnalysis)
    {
        $this->applicationsAnalysis = $applicationsAnalysis;
    }

    private function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->applicationsAnalysis->started_at = now();
            $this->applicationsAnalysis->status = 'Rodando...';
            $this->applicationsAnalysis->save(); // Save log in real-time

            // Command to start Docker container
            $dockerCommand = "docker run -i -v " . env('CACHE_DATA_PATH', 'atomic_shared_vol') . ":/home/node/app/result --rm analyzeragent:" . ((env('RUNNING_SERVER', 'PRODUCAO') == 'PRODUCAO') ? 'latest' : 'dev') . " node index.js {$this->applicationsAnalysis->application->url} --result_filename=" . str_replace('.', '_', $this->getDomain($this->applicationsAnalysis->application->url));

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
                $status = proc_close($process);

                if ($status !== 0) {
                    // If the exit status indicates an error, fail the job
                    $this->applicationsAnalysis->status = 'Erro.';
                    $this->applicationsAnalysis->save(); // Save log in real-time
                    $this->fail('O comando Docker (' . $dockerCommand . ') falhou com o status de saída: ' . $status);
                } else {
                    // If no errors, update finish information and status
                    $filePath = env('CACHE_DATA_PATH', '/shared/') . str_replace('.', '_', $this->getDomain($this->applicationsAnalysis->application->url)) . '.json';

                    // Check if the file exists
                    if (file_exists($filePath)) {
                        // Read the JSON content from the file
                        $jsonContent = file_get_contents($filePath);

                        // Parse the JSON content
                        $jsonData = json_decode($jsonContent, true);

                        if ($jsonData !== null) {
                            // Update the analysis
                            $this->applicationsAnalysis->analysis = $jsonData;
                            $this->applicationsAnalysis->finish_at = now();
                            $this->applicationsAnalysis->status = 'Finalizada.';
                            $this->applicationsAnalysis->save(); // Save log in real-time

                            // Update the application if necessary
                            $application = Applications::find($this->applicationsAnalysis->application_id);
                            if ($jsonData['possibleCMS']) {
                                $application->type = $jsonData['possibleCMSType'];
                            }
                            if ($jsonData['behindWAF']) {
                                $application->waf = $jsonData['behindWAFType'];
                            }
                            $application->save();


                        } else {
                            $this->applicationsAnalysis->status = 'Erro.';
                            $this->applicationsAnalysis->save(); // Save log in real-time
                            $this->fail('Erro ao fazer o parsing do JSON.');
                        }
                        // Delete the file
                        unlink($filePath);
                    } else {
                        $this->applicationsAnalysis->status = 'Erro.';
                        $this->applicationsAnalysis->save(); // Save log in real-time
                        $this->fail('O arquivo de resultado (' . $filePath . ') não foi encontrado.');
                    }
                }
            } else {
                $this->applicationsAnalysis->status = 'Erro.';
                $this->applicationsAnalysis->save(); // Save log in real-time
                $this->fail('Falha ao iniciar o processo Docker.');
            }
        } catch (\Exception $e) {
            $this->applicationsAnalysis->status = 'Erro.';
            $this->applicationsAnalysis->save();
            $this->fail('O trabalho falhou com a exceção: ' . $e->getMessage());
        }
    }
}