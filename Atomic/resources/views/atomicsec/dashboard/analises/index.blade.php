<x-dashboard-layout>
    <h2 class="mb-4">Análises @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
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
                <a href="{{ route('analysis.cadastrof', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Análise">
                    <i class="fa fa-fw fa-plus"></i>
                </a>
            </div>
            <table id="analises_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>URL</th>
                        <th>Data da Análise</th>
                        <th>Status</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analises as $analise)
                        <tr>
                            <td>{{ $analise->id }}</td>
                            <td>{{ $analise->application->name }}</td>
                            <td>{{ $analise->application->url }}</td>
                            @if($analise->finish_at)
                                <td>{{ \Carbon\Carbon::parse($analise->finish_at)->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</td>
                            @else
                                <td></td>
                            @endif
                            <td>{{ $analise->status }}</td>
                            <td>
                            <a href="{{ route('analysis.analise', ['id' => $analise->id]) }}" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Ver"><i class="fa fa-fw fa-eye"></i></a>
                            <a href="#" class="btn btn-icon btn-pill btn-danger" data-toggle="modal" data-target="#modal_delete" data-item="{{ $analise->id }}" title="Delete"><i class="fa fa-fw fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <x-atomicsec-modal 
        modal_id="modal_delete" 
        titulo="Deletar Análise" 
        confirm="true" 
        texto="Tem certeza que deseja deletar esta a análise?" 
        texto_confirmacao="Deletar" 
        texto_cancelar="Cancelar"
    ></x-atomicsec-modal>
    <x-slot name="extra_script">
        $(document).ready(function () {$('#analises_table').DataTable();});
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
                    "action": "{{ route('analysis.delete', '') }}/" + item
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