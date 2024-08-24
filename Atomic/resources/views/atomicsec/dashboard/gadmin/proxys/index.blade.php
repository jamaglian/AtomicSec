<x-dashboard-layout>
    <h2 class="mb-4">Proxys @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
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
                <a href="{{ route('gadmin_proxys.importForm', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Importar TXT">
                    <i class="fa fa-fw fa-plus"></i>
                </a>
            </div>
            <table id="proxys_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IP</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ultimo check</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proxys as $proxy)
                        <tr>
                            <td>{{ $proxy->id }}</td>
                            <td>{{ $proxy->ip }}</td>
                            <td>{{ $proxy->type }}</td>
                            <td>{{ (($proxy->working == 0)? (($proxy->tested_at == null)? 'Não Testado' : 'Não' ) : 'Sim') }}</td>
                            <td>{{ (($proxy->tested_at == null)? '-' : $proxy->tested_at) }}</td>
                            <td>
                                <a href="#" class="btn btn-icon btn-pill btn-danger" data-toggle="modal" data-target="#modal_delete" data-item="{{ $proxy->id }}" title="Delete"><i class="fa fa-fw fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <x-atomicsec-modal 
        modal_id="modal_delete" 
        titulo="Deletar Proxy" 
        confirm="true" 
        texto="Tem certeza que deseja deletar este proxy ?" 
        texto_confirmacao="Deletar" 
        texto_cancelar="Cancelar"
    ></x-atomicsec-modal>
    <x-slot name="extra_script">
        $(document).ready(function () {$('#proxys_table').DataTable();});
        $('#modal_delete').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Element that triggered the modal
            var item = button.data('item'); // Extract info from data-* attributes

            // Update the modal's content.
            var modal = $(this);
            modal.find('.modal-title').text(modal.find('.modal-title').text() + ' ( Proxy ' + item + ' )');

            // Pass the item to the delete function
            $('#modal_delete_confirm').off('click').on('click', function () {
                // Simulate a mouse click:
                //window.location.href = '';
                alert(' Falta programar a ação de deletar a empresa ' + item);
            });
        });
    </x-slot>
</x-dashboard-layout>