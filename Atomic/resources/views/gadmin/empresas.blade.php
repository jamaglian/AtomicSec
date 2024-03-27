<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Empresas') }}
        </h2>
    </x-slot>
    <style>
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Dono</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registros as $registro)
                            <tr>
                                <td>{{ $registro->id }}</td>
                                <td>{{ $registro->name }}</td>
                                <td>{{ $registro->owner->name }}</td>
                                <td>
                                    <a href="" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="" class="btn btn-sm btn-danger">Excluir</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
