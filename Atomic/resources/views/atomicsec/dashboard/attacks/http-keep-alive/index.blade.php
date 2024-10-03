<x-dashboard-layout>
    <h2 class="mb-4">HTTP Keep-Alive 
        @if(Auth::user()->isGlobalAdmin()) 
            <b class="text-danger"> (Global Admin) </b> 
        @endif 
        <a class="btn btn-icon btn-pill btn-outline-danger" data-toggle="collapse" href="#collapseExplain" role="button" aria-expanded="true" aria-controls="collapseExplain" data-original-title="Ver">
            <i class="fa fa-fw fa-question"></i>
        </a>
    </h2>
    <div class="collapse pb-2" id="collapseExplain" style="">
        <div class="card card-body">
            <h5><strong>Entendendo o Ataque HTTP Keep-Alive</strong></h5>
            <p>
                O ataque HTTP Keep-Alive explora uma característica presente no protocolo HTTP, que permite manter conexões abertas por um tempo prolongado, economizando recursos ao evitar múltiplos handshakes. 
                No entanto, atacantes mal-intencionados podem abusar dessa funcionalidade ao enviar pacotes de dados mínimos e manter a conexão aberta por longos períodos, esgotando os recursos do servidor sem a necessidade de gerar muito tráfego visível.
            </p>
            <h6><strong>Por que é Efetivo Mesmo com WAF?</strong></h6>
            <p>
                Muitos WAFs (Web Application Firewalls) são projetados para detectar e bloquear grandes volumes de solicitações maliciosas ou padrões de ataque conhecidos, mas o HTTP Keep-Alive é difícil de detectar porque não depende de grandes volumes de dados, e sim de uma abordagem mais sutil e prolongada.
            </p>
            <h6><strong>Como Minimizar o Impacto?</strong></h6>
            <ul>
                <li><strong>Limitação de Conexões Persistentes:</strong> Configurar o servidor para limitar o número de conexões simultâneas ou o tempo máximo permitido para conexões Keep-Alive pode evitar que recursos sejam consumidos por muito tempo.</li>
                <li><strong>Monitoramento de Conexões Inativas:</strong> Implementar monitoramento constante para identificar padrões de conexão inativa ou de baixa transmissão de dados pode ajudar a detectar tentativas de ataque.</li>
                <li><strong>Ajustes no Time-out:</strong> Reduzir o tempo de ociosidade permitido para conexões Keep-Alive antes de serem fechadas pelo servidor pode ajudar a reduzir o impacto desse tipo de ataque.</li>
            </ul>
            <p>
                Embora o HTTP Keep-Alive tenha seus benefícios em termos de performance, essas medidas ajudam a equilibrar a segurança, mitigando o potencial de ataques sem prejudicar os usuários legítimos.
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
                <a href="{{ route('ataques.http-keep-alive.cadratrof', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Análise">
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
                            <a href="{{ route('ataques.http-keep-alive.ataque', ['id' => $ataque->id]) }}" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Ver"><i class="fa fa-fw fa-eye"></i></a>
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