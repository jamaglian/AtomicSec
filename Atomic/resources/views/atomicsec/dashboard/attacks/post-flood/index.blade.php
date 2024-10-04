<x-dashboard-layout>
    <h2 class="mb-4">Post-Flood 
        @if(Auth::user()->isGlobalAdmin()) 
            <b class="text-danger"> (Global Admin) </b> 
        @endif 
        <a class="btn btn-icon btn-pill btn-outline-danger" data-toggle="collapse" href="#collapseExplain" role="button" aria-expanded="true" aria-controls="collapseExplain" data-original-title="Ver">
            <i class="fa fa-fw fa-question"></i>
        </a>
    </h2>
    <div class="collapse pb-2" id="collapseExplain" style="">
        <div class="card card-body">
            <h5><strong>Entendendo o Ataque POST Flood</strong></h5>
            <p>
                O ataque POST Flood é um tipo de ataque DDoS (Distributed Denial of Service) que sobrecarrega o servidor ao enviar um grande volume de requisições POST em um curto espaço de tempo. Cada requisição POST contém dados que precisam ser processados, resultando em um alto consumo de recursos como CPU, memória e largura de banda, o que pode deixar o servidor lento ou até mesmo indisponível.
            </p>
            <h6><strong>Por que o POST Flood é Perigoso?</strong></h6>
            <p>
                Como as requisições POST geralmente envolvem processamento no lado do servidor (como gravação de dados ou geração de respostas dinâmicas), elas exigem mais recursos do que requisições GET. Um ataque de POST Flood pode rapidamente exaurir os recursos do servidor, afetando o desempenho e a capacidade de atender a usuários legítimos. Além disso, o ataque pode ser distribuído entre várias máquinas, tornando difícil bloquear todos os IPs envolvidos.
            </p>
            <h6><strong>Como Minimizar o Impacto?</strong></h6>
            <ul>
                <li><strong>Rate Limiting:</strong> Implementar limites de taxa (rate limiting) por IP ou sessão pode ajudar a evitar que um único atacante ou um grupo de atacantes sobrecarregue o servidor com um grande volume de requisições POST.</li>
                <li><strong>CAPTCHA em Formulários:</strong> Adicionar CAPTCHAs em formulários que utilizam requisições POST pode impedir que bots automatizados realizem ataques de POST Flood em massa.</li>
                <li><strong>Verificação de Padrões Anômalos:</strong> Monitorar o tráfego em busca de padrões incomuns, como um grande número de requisições POST vindas de poucos IPs em um curto período, permite identificar e bloquear ataques rapidamente.</li>
                <li><strong>Filtragem por WAF:</strong> Configurar um Web Application Firewall (WAF) para detectar e bloquear requisições POST excessivas ou que apresentem padrões de ataque pode mitigar o impacto de POST Floods.</li>
                <li><strong>Cacheamento de Respostas:</strong> Sempre que possível, cachear as respostas das requisições POST pode reduzir a carga do servidor, já que respostas repetidas não precisariam ser processadas novamente.</li>
            </ul>
            <p>
                O POST Flood pode ser devastador quando um servidor não está preparado para lidar com grandes volumes de requisições, mas com as medidas corretas de limitação de taxa, autenticação via CAPTCHA e monitoramento ativo, é possível mitigar seus efeitos e proteger o servidor.
            </p>
        </div>
    </div>

    <div class="alert alert-danger" role="alert"> 
        <div class="d-flex justify-content-center">
            <h4 class="alert-heading">Área de risco!</h4>
        </div>
        <div class="d-flex justify-content-center">
            <b>O ataque pode causar indisponibilidade do serviço, lentidão e até mesmo a quebra do serviço. Só utilize esta ferramenta se tiver certeza do que está fazendo.</b>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            @endif
            @if(session('fail'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('fail') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            @endif
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('ataques.post-flood.cadratrof', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Análise">
                    <i class="fa fa-fw fa-plus"></i>
                </a>
            </div>
            <table id="attack_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>URL</th>
                        <th>Data do Ataque</th>
                        <th>Status</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ataques as $ataque)
                        <tr>
                            <td>{{ $ataque->id }}</td>
                            <td>{{ $ataque->application->name }}</td>
                            <td>{{ $ataque->application->url }}</td>
                            @if($ataque->finish_at)
                                <td>{{ \Carbon\Carbon::parse($ataque->finish_at)->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</td>
                            @else
                                <td></td>
                            @endif
                            <td>{{ $ataque->status }}</td>
                            <td>
                            <a href="{{ route('ataques.post-flood.ataque', ['id' => $ataque->id]) }}" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Ver"><i class="fa fa-fw fa-eye"></i></a>
                            <a href="#" class="btn btn-icon btn-pill btn-danger" data-toggle="modal" data-target="#modal_delete" data-item="{{ $ataque->id }}" title="Delete"><i class="fa fa-fw fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <x-atomicsec-modal 
        modal_id="modal_delete" 
        titulo="Deletar Ataque" 
        confirm="true" 
        texto="Tem certeza que deseja deletar este ataque?" 
        texto_confirmacao="Deletar" 
        texto_cancelar="Cancelar"
    ></x-atomicsec-modal>
    <x-slot name="extra_script">
        $(document).ready(function () {$('#attack_table').DataTable();});
        $('#modal_delete').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Element that triggered the modal
            var item = button.data('item'); // Extract info from data-* attributes

            // Update the modal's content.
            var modal = $(this);
            modal.find('.modal-title').text(modal.find('.modal-title').text() + ' ( Ataque ' + item + ' )');

            // Pass the item to the delete function
            $('#modal_delete_confirm').off('click').on('click', function () {
                var token = $('meta[name="csrf-token"]').attr('content'); // Assuming you have CSRF token in a meta tag

                $('<form>', {
                    "method": "post",
                    "action": "{{ route('ataques.delete', '') }}/" + item
                }).append($('<input>', {
                    "type": "hidden",
                    "name": "_method",
                    "value": "delete"
                })).append($('<input>', {
                    "type": "hidden",
                    "name": "_token",
                    "value": token
                })).appendTo(document.body).submit();
            });
        });
    </x-slot>
</x-dashboard-layout>