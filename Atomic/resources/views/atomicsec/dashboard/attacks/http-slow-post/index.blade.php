<x-dashboard-layout>
    <h2 class="mb-4">HTTP Slow-POST 
        @if(Auth::user()->isGlobalAdmin()) 
            <b class="text-danger"> (Global Admin) </b> 
        @endif 
        <a class="btn btn-icon btn-pill btn-outline-danger" data-toggle="collapse" href="#collapseExplain" role="button" aria-expanded="true" aria-controls="collapseExplain" data-original-title="Ver">
            <i class="fa fa-fw fa-question"></i>
        </a>
    </h2>
    <div class="collapse pb-2" id="collapseExplain" style="">
        <div class="card card-body">
            <h5><strong>Entendendo o Ataque Slow POST</strong></h5>
            <p>
                O ataque Slow POST explora a maneira como os servidores processam os pedidos HTTP POST, onde o atacante envia dados de formulário de forma extremamente lenta. O servidor, por padrão, espera até que o corpo da solicitação POST seja completamente recebido, o que permite que atacantes mantenham uma conexão aberta por um longo período sem concluir o envio de dados, esgotando os recursos do servidor.
            </p>
            <h6><strong>Por que é Difícil de Detectar?</strong></h6>
            <p>
                Ao contrário de ataques tradicionais de negação de serviço, o Slow POST não depende de um grande volume de tráfego, o que torna mais difícil para os WAFs (Web Application Firewalls) detectarem automaticamente. Além disso, o comportamento de envio lento simula uma conexão legítima, dificultando a diferenciação entre um usuário genuíno e um atacante.
            </p>
            <h6><strong>Como Minimizar o Impacto?</strong></h6>
            <ul>
                <li><strong>Ajuste de Timeouts:</strong> Configurar tempos limite (timeouts) mais curtos para recebimento de dados de POST pode ajudar a fechar conexões que estão tentando se arrastar lentamente, minimizando o consumo de recursos.</li>
                <li><strong>Limite de Tamanho de Requisição:</strong> Estabelecer limites de tamanho para os dados POST e monitorar a taxa de upload pode identificar tentativas de envio extremamente lento e bloquear o ataque.</li>
                <li><strong>Monitoramento de Conexões Lentamente Ativas:</strong> Implementar ferramentas de monitoramento que identifiquem conexões que enviam dados de forma irregular ou em baixa velocidade pode ajudar a detectar tentativas de Slow POST.</li>
                <li><strong>Uso de Servidores Proxy:</strong> Utilizar proxies configurados com limitação de tempo e taxa de envio pode proteger o servidor principal contra essas tentativas de ataque.</li>
            </ul>
            <p>
                O ataque Slow POST é efetivo devido à sua natureza furtiva, mas com o ajuste adequado de tempos limite e monitoramento de conexões lentas, é possível minimizar significativamente seu impacto sem afetar os usuários legítimos.
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
                <a href="{{ route('ataques.http-slow-post.cadratrof', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Análise">
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
                            <a href="{{ route('ataques.http-slow-post.ataque', ['id' => $ataque->id]) }}" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Ver"><i class="fa fa-fw fa-eye"></i></a>
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