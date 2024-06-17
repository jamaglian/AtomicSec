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

    private function getDomain($url){
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if(preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)){
            return $regs['domain'];
        }
        return FALSE;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->applicationsAnalysis->started_at = now();
        $this->applicationsAnalysis->status = 'Rodando...';
        $this->applicationsAnalysis->save(); // Save log in real-time
        // Command to start Docker container (Argumento t removido)
        $dockerCommand = "docker run -i -v " . env('CACHE_DATA_PATH', 'atomic_shared_vol') . ":/home/node/app/result --rm analyzeragent:" . ((env('CACHE_DATA_PATH', 'PRODUCAO') == 'PRODUCAO')? 'latest':'dev') ." node index.js {$this->applicationsAnalysis->application->url} --result_filename=". str_replace('.', '_', $this->getDomain($this->applicationsAnalysis->application->url));
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
                // Se o status de saída indicar um erro, falhe o job
                $this->applicationsAnalysis->status = 'Erro.';
                $this->applicationsAnalysis->save(); // Save log in real-time
                $this->fail('O comando Docker (' . $dockerCommand . ') falhou com o status de saída: ' . $status);
            } else {
                // Se não houver erros, atualize as informações de finalização e status
                $filePath = env('CACHE_DATA_PATH', '/shared/') . str_replace('.', '_', $this->getDomain($this->applicationsAnalysis->application->url)) . '.json';
                // Verifica se o arquivo existe
                if (file_exists($filePath)) {
                    // Lê o conteúdo do arquivo JSON
                    $jsonContent = file_get_contents($filePath);

                    // Faz o parsing do JSON para um array associativo
                    $jsonData = json_decode($jsonContent, true);
                     // Verifica se o parsing foi bem-sucedido
                    if ($jsonData !== null) {

                        // Ou, se já tiver um modelo existente
                        /*
                        $modelo = SeuModelo::find($id);
                        $modelo->coluna_json = $jsonData;
                        $modelo->save();
                        */
                        $this->applicationsAnalysis->analysis = $jsonData;
                        $this->applicationsAnalysis->finish_at = now();
                        $this->applicationsAnalysis->status = 'Finalizada.';
                        $this->applicationsAnalysis->save(); // Save log in real-time
                        if($jsonData['possibleCMS']){
                            $application = Applications::find($this->applicationsAnalysis->application_id);
                            $application->type = $jsonData['possibleCMSType'];
                            $application->save();
                        }
                        unlink($filePath);
                    } else {
                        $this->applicationsAnalysis->status = 'Erro.';
                        $this->applicationsAnalysis->save(); // Save log in real-time
                        $this->fail('Erro ao fazer o parsing do JSON.');  
                    }
                }else{
                    $this->applicationsAnalysis->status = 'Erro.';
                    $this->applicationsAnalysis->save(); // Save log in real-time
                    $this->fail('O arquivo de resultado (' . $filePath . ') não foi encontrado.');  
                }
                
            }
        }
    }
}
