<x-dashboard-layout>
    <h2 class="mb-4">XML RPC Flood (Suporte apenas para wordpress nesse momento)
        @if(Auth::user()->isGlobalAdmin()) 
            <b class="text-danger"> (Global Admin) </b> 
        @endif 
        <a class="btn btn-icon btn-pill btn-outline-danger" data-toggle="collapse" href="#collapseExplain" role="button" aria-expanded="true" aria-controls="collapseExplain" data-original-title="Ver">
            <i class="fa fa-fw fa-question"></i>
        </a>
    </h2>
    <div class="collapse pb-2" id="collapseExplain" style="">
        <div class="card card-body">
            <h5><strong>Entendendo o Ataque XML-RPC Flood</strong></h5>
            <p>
                O ataque XML-RPC Flood é um tipo de negação de serviço (DoS) que explora a API XML-RPC, frequentemente usada em sistemas de gerenciamento de conteúdo como o WordPress. O protocolo XML-RPC permite que clientes remotos façam múltiplas chamadas de funções em uma única requisição. Atacantes podem abusar dessa funcionalidade para enviar um grande número de chamadas em massa, sobrecarregando o servidor e esgotando seus recursos.
            </p>
            <h6><strong>Por que o XML-RPC Flood é Eficiente?</strong></h6>
            <p>
                A funcionalidade do XML-RPC, especialmente o método <code>system.multicall</code>, permite que múltiplas operações sejam executadas simultaneamente. Isso pode ser utilizado para enviar centenas ou até milhares de requisições em uma única chamada, o que força o servidor a processar um volume massivo de dados. Como resultado, o ataque pode rapidamente consumir CPU e memória, tornando o site lento ou inacessível.
            </p>
            <h6><strong>Como Minimizar o Impacto?</strong></h6>
            <ul>
                <li><strong>Desabilitar o XML-RPC:</strong> Se o XML-RPC não for necessário para o funcionamento do site, desabilitá-lo completamente é uma das formas mais eficazes de se proteger contra esse tipo de ataque.</li>
                <li><strong>Bloquear o Método Multicall:</strong> Se o XML-RPC for essencial para o site, você pode bloquear o uso do método <code>system.multicall</code>, que é comumente explorado nesses ataques.</li>
                <li><strong>Implementar Rate Limiting:</strong> Limitar o número de requisições XML-RPC permitidas por IP em um determinado período pode reduzir a eficácia do ataque, impedindo que um único IP envie múltiplas chamadas rapidamente.</li>
                <li><strong>Monitoramento e Filtragem de Requisições:</strong> Configurar o servidor para monitorar chamadas XML-RPC suspeitas e bloquear requisições excessivas ou padrões anômalos pode mitigar o ataque em tempo real.</li>
                <li><strong>Uso de WAF:</strong> Um Web Application Firewall (WAF) bem configurado pode identificar e bloquear ataques XML-RPC, especialmente aqueles que utilizam múltiplas chamadas de função em uma única requisição.</li>
            </ul>
            <p>
                O XML-RPC Flood é uma técnica poderosa que pode ser devastadora quando explorada de forma maliciosa, mas desativar ou proteger adequadamente a API XML-RPC e implementar limites e monitoramento são passos essenciais para minimizar o impacto desse ataque.
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
                <a href="{{ route('ataques.xml-rpc-flood.cadratrof', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Análise">
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
                            <a href="{{ route('ataques.xml-rpc-flood.ataque', ['id' => $ataque->id]) }}" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Ver"><i class="fa fa-fw fa-eye"></i></a>
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