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
                <div id="chart_div_links"></div>
                <b>Informações por link:</b>
                <table id="analises_table" class="table table-hover" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>Forms</th>
                            <th>Tempo Médio (ms)</th>
                            <th>Tempo Por Rodada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($links_encontrados as $key => $links)
                        <tr>
                            <td>{{$key}}</td>
                            <td>
                                @php
                                    echo count($links->forms);
                                @endphp
                            </td>
                            <td>{{$links->media}}</td>
                            <td>
                                @foreach($links->times as $key => $time)
                                    <dd><b>{{$key + 1}}:</b> {{$time->serverProcessingTime}}ms </dd>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="accordion pt-4" id="accordionFormularios">
                    <b>Formulários encontrados nos links:</b>
                    <div class="card">
                        @foreach($links_encontrados as $key => $links)
                            @if(count($links->forms) > 0)
                                <div class="card-header font-weight-bold" id="headingFormularios-{{$loop->iteration}}">
                                    <a href="#" class="collapsed" data-toggle="collapse" data-target="#collapseFormularios-{{$loop->iteration}}" aria-expanded="false" aria-controls="collapseFormularios-{{$loop->iteration}}">
                                        <div class="row">
                                            <div class="col">
                                                {{$key}}
                                            </div>
                                            <div class="col-auto collapse-icon"></div>
                                        </div>
                                    </a>
                                </div>
                                <div id="collapseFormularios-{{$loop->iteration}}" class="collapse" aria-labelledby="headingFormularios-{{$loop->iteration}}" data-parent="#accordionFormularios">
                                    <div class="card-body">
                                        <div class="row">
                                        @php
                                            $formsAdded = 0;
                                        @endphp
                                        @foreach($links->forms as $keyf => $from)
                                            <div class="col-md-6 border">
                                                <b>Metodo:</b> {{$from->params->method}} <br>
                                                <b>URL(Action):</b> {{$from->params->url}} <br>
                                                <b>Campos:</b> <br>
                                                <pre>{{ json_encode($from->params->formData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            @php $formsAdded++; @endphp
                                            @if($formsAdded % 2 == 0)
                                                </div>
                                                <div class="row">
                                            @endif
                                        @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="accordion pt-4" id="accordionLogs">
                <div class="card">
                    <div class="card-header font-weight-bold" id="headingLogsCompletas">
                        <a href="#"  @if($analise->status == 'Finalizada.') class="collapsed" @endif data-toggle="collapse" data-target="#collapseLogsCompletas" @if($analise->status == 'Finalizada.') aria-expanded="false" @else aria-expanded="true" @endif aria-controls="collapseLogsCompletas">
                            <div class="row">
                                <div class="col">
                                    Logs Completas
                                </div>
                                <div class="col-auto collapse-icon"></div>
                            </div>
                        </a>
                    </div>
                    <div id="collapseLogsCompletas" class="collapse @if($analise->status != 'Finalizada.') show @endif" aria-labelledby="headingLogsCompletas" data-parent="#accordionLogs">
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
                pageLength: 6,
                responsive: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.5/i18n/pt-BR.json"
                }
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
                    title: 'Links por impacto',
                    width: '100%',
                    height: '100%'
                };

                var chart = new google.visualization.PieChart(document.getElementById('chart_div_links'));

                chart.draw(data, options);
            }
            window.onresize = function() {
                drawLinksChart();
            };
        @endif
        @if($analise->status == 'Rodando...' || $analise->status == 'Pendente')
            setTimeout(function () {
                location.reload()
            }, 5000);
        @endif
    </x-slot>
</x-dashboard-layout>