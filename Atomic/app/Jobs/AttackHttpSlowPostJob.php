<?php

namespace App\Jobs;

use App\Models\ApplicationAttack;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
            $this->applicationsAttack->save(); // Salva o log em tempo real
            $params = json_decode($this->applicationsAttack->attack_params, true);
            $attackCommand = env('ATTACKS_DATA_PATH', '/var/www/html/bin/attacks/') . "HTTP_Slow_Post -url={$params['url']} -params='{$params['params_post']}' -workers={$params['atacantes']} -process-timeout={$params['tempo']} " . (($params['use_proxy'] == 'yes' && env('PROXY_TO_USE', '') != '')? "-proxies=" . env('PROXY_TO_USE', ''):'');
            //$attackCommand = env('ATTACKS_DATA_PATH', '/var/www/html/bin/attacks/') . "HTTP_Keep_Alive -url={$this->applicationsAttack->application->url} -threads={$params['atacantes']} -process-timeout={$params['tempo']} " . (($params['use_proxy'] == 'yes' && env('PROXY_TO_USE', '') != '')? "-proxies=" . env('PROXY_TO_USE', ''):'');
            $process = proc_open($attackCommand, [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w']   // stderr
            ], $pipes);
            fclose($pipes[0]);
            $this->applicationsAttack->log .= $attackCommand;
            $this->applicationsAttack->save();
            if (is_resource($process)) {
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    // Salva a saída do contêiner Docker no banco de dados
                    $this->applicationsAttack->log .= $line;
                    $this->applicationsAttack->save();
                }
                // Fecha o pipe
                fclose($pipes[1]);
                fclose($pipes[2]);
                // Fecha o processo
                $status = proc_close($process);
                if ($status !== 0) {
                    $this->applicationsAttack->log .= 'O processo encerrou com erro.';
                    $this->applicationsAttack->status = 'Erro.';
                    $this->applicationsAttack->save();
                    $this->fail('O processo encerrou com erro.');
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
}
