<x-dashboard-layout>
    <h2 class="mb-4">Aplicações @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
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
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('aplicacoes.cadastrarf', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Aplicação">
                    <i class="fa fa-fw fa-plus"></i>
                </a>
            </div>
            <table id="aplicacoes_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>URL</th>
                        <th>Tipo</th>
                        <th>WAF</th>
                        <th>Ultima Analize</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $aplication)
                        <tr>
                            <td>{{ $aplication->id }}</td>
                            <td>{{ $aplication->name }}</td>
                            <td>{{ $aplication->url }}</td>
                            <td>{{ $aplication->type }}</td>
                            <td>{{ $aplication->waf }}</td>
                            @if($aplication->analysis->isNotEmpty() && $aplication->analysis->sortByDesc('created_at')->first()->finish_at)
                                <td>{{ \Carbon\Carbon::parse($aplication->analysis->sortByDesc('created_at')->first()->finish_at)->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</td>
                            @else
                                <td>N/A</td>
                            @endif
                            <td>
                            <!-- a href="#" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Edit"><i class="fa fa-fw fa-edit"></i></a -->
                            <a href="#" class="btn btn-icon btn-pill btn-danger" data-toggle="modal" data-target="#modal_delete" data-item="{{ $aplication->id }}" title="Delete"><i class="fa fa-fw fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <x-atomicsec-modal 
        modal_id="modal_delete" 
        titulo="Deletar Aplicação" 
        confirm="true" 
        texto="Tem certeza que deseja deletar esta aplicação?" 
        texto_confirmacao="Deletar" 
        texto_cancelar="Cancelar"
    ></x-atomicsec-modal>
    <x-slot name="extra_script">
    $(document).ready(function () {$('#aplicacoes_table').DataTable();});
    $('#modal_delete').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Element that triggered the modal
        var item = button.data('item'); // Extract info from data-* attributes

        // Update the modal's content.
        var modal = $(this);
        modal.find('.modal-title').text(modal.find('.modal-title').text() + ' ( Aplicação ' + item + ' )');

        // Pass the item to the delete function
        $('#modal_delete_confirm').off('click').on('click', function () {
            var token = $('meta[name="csrf-token"]').attr('content'); // Assuming you have CSRF token in a meta tag

            $('<form>', {
                "method": "post",
                "action": "{{ route('aplicacoes.delete', '') }}/" + item
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