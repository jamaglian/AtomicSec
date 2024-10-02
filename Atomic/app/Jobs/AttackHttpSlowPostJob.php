<?php

namespace App\Jobs;

use App\Models\ApplicationAttack;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AttackHttpSlowPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ApplicationAttack $applicationsAttack;
    // Timeout para o job (em segundos)
    public $timeout = 3600;
    /**
     * Create a new job instance.
     */
    public function __construct(ApplicationAttack $applicationsAttack)
    {
        $this->applicationsAttack = $applicationsAttack;
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
        try{
            $this->applicationsAttack->started_at = now();
            $this->applicationsAttack->status = 'Rodando...';
            $this->applicationsAttack->job_uuid = $this->job->uuid();
            $this->applicationsAttack->save(); // Salva o log em tempo real
            $params = json_decode($this->applicationsAttack->attack_params, true);
            //Inicia com o caminho base dos binários de ataques.
            $attackCommand = env('ATTACKS_DATA_PATH', '/var/www/html/bin/attacks/');
            //Adiciona o nome do binário do ataque bem como os parametros obrigátórios.
            $attackCommand .= "HTTP_Slow_Post -url=\"{$params['url']}\" -params=\"{$params['params_post']}\" -workers={$params['atacantes']} -process-timeout={$params['tempo']} ";
            //Adiciona o proxy se aplicável.
            $attackCommand .= (($params['use_proxy'] == 'yes' && env('PROXY_TO_USE', '') != '')? "-proxies=" . env('PROXY_TO_USE', ''):'');
            //Adiciona o tamanho do corpo da requisição.
            $attackCommand .= " -bodysize=" . $params['tamanho_corpo'];
            // Abre um pipe para o processo Docker
            $process = proc_open($attackCommand, [1 => ['pipe', 'w']], $pipes);

            if (is_resource($process)) {
                $buffer = ''; // Buffer para acumular a saída
                $linesToSave = 50; // Número de linhas a acumular antes de salvar
                // Pega o PID do processo
                $status = proc_get_status($process);
                $this->applicationsAttack->pid = $status['pid'] + 1; // O PID do processo
                $this->applicationsAttack->save();
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    $buffer .= $line;
                
                    // Verifica se atingiu o número de linhas para salvar
                    if (substr_count($buffer, "\n") >= $linesToSave) {
                        $this->applicationsAttack->log .= $buffer;
                        $this->applicationsAttack->save();
                        $buffer = ''; // Limpa o buffer após salvar
                    }
                }
                
                // Salva qualquer resto que possa estar no buffer
                if (!empty($buffer)) {
                    $this->applicationsAttack->log .= $buffer;
                    $this->applicationsAttack->save();
                }
                
                // Fecha o pipe
                fclose($pipes[1]);
                // Fecha o processo
                $status = proc_close($process);
                if ($status !== 0) {
                    $this->applicationsAttack->log .= 'O processo encerrou com erro.';
                    $this->applicationsAttack->status = 'Erro.';
                    $this->applicationsAttack->save();
                    $this->fail('O processo encerrou com erro. ('. $attackCommand .')');
                    return;
                }
                $this->applicationsAttack->status = 'Finalizado.';
                $this->applicationsAttack->finish_at = now();
                $this->applicationsAttack->save();
            }else{
                $this->applicationsAttack->log .= 'Erro ao abrir o processo.';
                $this->applicationsAttack->status = 'Erro.';
                $this->applicationsAttack->save();
                $this->fail('O trabalho falhou ao abrir o processo.');
            }
        }catch(\Exception $e){
            $this->applicationsAttack->status = 'Erro.';
            $this->applicationsAttack->save();
            $this->fail('O trabalho falhou com a exceção: ' . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $this->applicationsAttack->status = 'Erro.';
        $this->applicationsAttack->save();
        $this->fail('O trabalho falhou com a exceção: ' . $exception->getMessage());
        // Send user notification of failure, etc...
    }
}
