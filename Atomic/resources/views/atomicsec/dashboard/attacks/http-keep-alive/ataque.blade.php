<x-dashboard-layout >
    <h2 class="mb-4">Ataque HTTP Keep-Alive @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center bg-white font-weight-bold">
            <div>
                {{ __('Ataque') }}
                @if($attack->status == 'Pendente')
                    <span class="badge rounded-pill bg-secondary "><b>Status:</b> Pendente</span>
                @elseif($attack->status == 'Rodando...')
                    <span class="badge rounded-pill bg-primary "><b>Status:</b> Em Andamento</span>
                @elseif($attack->status == 'Finalizado.')
                    <span class="badge rounded-pill bg-success "><b>Status:</b> Finalizado.</span>
                @elseif($attack->status == 'Erro.')
                    <span class="badge rounded-pill bg-danger "><b>Status:</b> Erro.</span>
                @endif
            </div>
            @if($attack->status == 'Rodando...')
                <a href="#" class="btn btn-danger btn-lg btn-icon" data-toggle="tooltip" title="" data-original-title="Cancelar Ataque">
                    <i class="fa fa-fw fa-ban"></i>
                </a> 
                <!--
                    // Caso SIGTERM falhe, tenta forçar com SIGKILL
                    exec("kill -9 $pid", $output, $return_var);

                    if ($return_var === 0) {
                        echo "Processo com PID $pid foi forçadamente encerrado (SIGKILL).";
                    } else {
                        echo "Falha ao encerrar o processo com PID $pid.";
                    }
                -->
            @endif
        </div>
        <div class="card-body">
            <div class="accordion pt-4" id="accordionLogs">
                <div class="card">
                    <div class="card-header font-weight-bold" id="headingLogsCompletas">
                        <a href="#"  @if($attack->status == 'Finalizado.') class="collapsed" @endif data-toggle="collapse" data-target="#collapseLogsCompletas" @if($attack->status == 'Finalizado.') aria-expanded="false" @else aria-expanded="true" @endif aria-controls="collapseLogsCompletas">
                            <div class="row">
                                <div class="col">
                                    Logs Completas
                                </div>
                                <div class="col-auto collapse-icon"></div>
                            </div>
                        </a>
                    </div>
                    <div id="collapseLogsCompletas" class="collapse @if($attack->status != 'Finalizado.') show @endif" aria-labelledby="headingLogsCompletas" data-parent="#accordionLogs">
                        <div class="card-body">
                            <textarea id="w3review" style="width: 100%; height: 500px;" name="w3review" rows="30" cols="130" disabled>
                                {{$attack->log}}
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="extra_script">
        @if($attack->status == 'Rodando...' || $attack->status == 'Pendente')
            setTimeout(function () {
                location.reload()
            }, 3000);
        @endif
    </x-slot>
</x-dashboard-layout>