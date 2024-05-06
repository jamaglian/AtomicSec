<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Aplicações') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{ session('success') }}</span>
                    @endif
                    <div class="justify-end flex items-center gap-4 gap-x-6 mb-4">
                        <a type="submit" href="{{ route('aplicacoes.cadastrarf', absolute: false) }}" style="cursor: pointer;" class="cursor-pointer inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Cadastrar Nova
                        </a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>URL</th>
                                <th>Tipo</th>
                                <th>Ultima Analize</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $aplication)
                            <tr>
                                <td>{{ $aplication->name }}</td>
                                <td>{{ $aplication->url }}</td>
                                <td>{{ $aplication->type }}</td>
                                @if($aplication->analysis->isNotEmpty() && $aplication->analysis->sortByDesc('created_at')->first()->finish_at)
                                    <td>{{ \Carbon\Carbon::parse($aplication->analysis->sortByDesc('created_at')->first()->finish_at)->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}</td>
                                @else
                                    <td>N/A</td>
                                @endif
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
