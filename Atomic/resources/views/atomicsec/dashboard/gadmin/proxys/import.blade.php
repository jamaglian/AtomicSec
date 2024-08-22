<x-dashboard-layout>
    <h2 class="mb-4">Importar Proxys @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="card mb-4">
        <div class="card-header bg-white font-weight-bold">
            Formulário de Importação
        </div>
        <div class="card-body">
            <form id="cadastro_c_form" method="POST" action="{{ route('gadmin_proxys.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <x-atomicsec-input
                        id_input="proxy_file"
                        type="file" 
                        name="proxy_file" 
                        :value="old('proxy_file')"  
                        :placeholder="__('Arquivo de Proxies (.txt)')"
                        :label_text="__('Arquivo de Proxies (.txt)')" 
                        col_classes="col-md-6 mb-3"
                        required 
                        autofocus 
                        :messages="$errors->get('proxy_file')" 
                    />
                    <div class="col-md-6 mb-3">
                        <label for="type">Tipo de Proxy:</label>
                        <div class="input-group ">
                            <select name="type" class="form-control" required>
                                <option value="http">HTTP</option>
                                <option value="https">HTTPS</option>
                                <option value="socks4">SOCKS4</option>
                                <option value="socks5">SOCKS5</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary" type="submit" onclick=" document.getElementById('cadastro_c_form').submit() ">Cadastrar</button>
        </div>
    </div>
</x-dashboard-layout>