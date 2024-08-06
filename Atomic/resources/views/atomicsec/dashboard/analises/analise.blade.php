<x-dashboard-layout >
    <h2 class="mb-4">Análises @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header bg-white font-weight-bold">
            {{ __('Analise') }}
            @if($analise->status == 'Pendente')
                <span class="badge rounded-pill bg-secondary "><b>Status:</b> Pendente</span>
            @elseif($analise->status == 'Rodando...')
                <span class="badge rounded-pill bg-primary "><b>Status:</b> Em Andamento</span>
            @elseif($analise->status == 'Finalizada.')
                <span class="badge rounded-pill bg-success "><b>Status:</b> Finalizada.</span>
            @elseif($analise->status == 'Erro.')
                <span class="badge rounded-pill bg-danger "><b>Status:</b> Erro.</span>
            @endif
        </div>
        <div class="card-body">
            @if($links_encontrados != null)
                <div id="chart_div_links" style="width: 100%; height: 500px;"></div>

                <b>Informações por link:</b>
                <table id="analises_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>Tempo Médio</th>
                            <th>Tempo Por Rodada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($links_encontrados as $key => $links)
                        <tr>
                            <td>{{$key}}</td>
                            <td>{{$links->media}}ms</td>
                            <td>
                                @foreach($links->times as $key => $time)
                                    <dd><b>{{$key + 1}}:</b> {{$time->serverProcessingTime}}ms </dd>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
            @endif
            <div class="accordion" id="accordionLogs">
                <div class="card">
                    <div class="card-header font-weight-bold" id="headingLogsCompletas">
                        <a href="#" data-toggle="collapse" data-target="#collapseLogsCompletas" aria-expanded="true" aria-controls="collapseLogsCompletas">
                            <div class="row">
                                <div class="col">
                                    Logs Completas
                                </div>
                                <div class="col-auto collapse-icon"></div>
                            </div>
                        </a>
                    </div>
                    <div id="collapseLogsCompletas" class="collapse show" aria-labelledby="headingLogsCompletas" data-parent="#accordionLogs">
                        <div class="card-body">
                            <textarea id="w3review" style="width: 100%; height: 500px;" name="w3review" rows="30" cols="130" disabled>
                                {{$analise->log}}
                            </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-slot name="extra_script">
        $(document).ready(function () {
            $('#analises_table').DataTable({
                pageLength: 3,
                'dom': 'rtip'
            });
        });
        @if($links_encontrados != null)
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(drawLinksChart);
            function drawLinksChart() {
                var data = google.visualization.arrayToDataTable([
                    ['Link', 'Tempo Médio'],  // Cabeçalho da coluna
                    @foreach($links_encontrados as $key => $links)
                        ['{{$key}}', {{$links->media}}],
                    @endforeach
                ]);

                var options = {
                    title: 'Links por impacto'
                };

                var chart = new google.visualization.PieChart(document.getElementById('chart_div_links'));

                chart.draw(data, options);
            }
        @endif
        @if($analise->status == 'Rodando...' || $analise->status == 'Pendente')
            setTimeout(function () {
                location.reload()
            }, 5000);
        @endif
    </x-slot>
</x-dashboard-layout>