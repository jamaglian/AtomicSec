<x-dashboard-layout>
    <h2 class="mb-4">Empresas @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
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
                <a href="{{ route('gadmin_companies.registerf', absolute: false) }}" class="btn btn-primary btn-lg btn-icon" data-toggle="tooltip" title="Adicionar Empresa">
                    <i class="fa fa-fw fa-plus"></i>
                </a>
            </div>
            <table id="empresas_table" class="table table-hover" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Dono</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $registro)
                        <tr>
                            <td>{{ $registro->id }}</td>
                            <td>{{ $registro->name }}</td>
                            <td>{{ $registro->owner->name }}</td>
                            <td>
                            <a href="#" class="btn btn-icon btn-pill btn-primary" data-toggle="tooltip" title="Edit"><i class="fa fa-fw fa-edit"></i></a>
                            <a href="#" class="btn btn-icon btn-pill btn-danger" data-toggle="tooltip" title="Delete"><i class="fa fa-fw fa-trash"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-dashboard-layout>