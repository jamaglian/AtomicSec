<x-dashboard-layout>
    <h2 class="mb-4">Cadastrar Ataque HTTP Slow-POST @if(Auth::user()->isGlobalAdmin()) <b class="text-danger"> (Global Admin) </b> @endif </h2>
    <div class="alert alert-danger" role="alert"> 
        <div class="d-flex justify-content-center">
            <h4 class="alert-heading">Área de risco!</h4>
        </div>
        <div class="d-flex justify-content-center">
            <b>O ataque pode causar indisponibilidade do serviço, lentidão e até mesmo a quebra do serviço. Só utilize esta ferramenta se tiver certeza do que está fazendo.</b>
        </div>
    </div>
    <div class="card mb-4">
        @if(count($applications) > 0)
            <div class="card-header bg-white font-weight-bold">
            {{ __('Ataque HTTP Slow-POST para Aplicação da empresa ')}} <b> {{$company->name}} </b>
            </div>
            <div class="card-body">
                <form id="atacar_form" method="POST" action="{{ route('ataques.http-slow-post.cadastro') }}">
                    @csrf
                    <div class="form-group">
                        <label for="aplicacao">Escolha uma aplicação: </label>
                        <select class="form-control" id="aplicacao" name="aplicacao">
                            @foreach($applications as $application)
                                <option value="{{$application->id}}">{{$application->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="resto_form" style="visibility: hidden;">
                        <div class="form-group">
                            <label for="atacantes">Número de atacantes: </label>
                            <input class="form-control" type="number" id="atacantes" name="atacantes" value="500">
                        </div>
                        <div class="form-group">
                            <label for="use_proxy">Usar Proxy (Para aplicações com WAF essa configuração não tem efeito.): </label>
                            <select class="form-control" id="use_proxy" name="use_proxy">
                                <option value="yes">Sim</option>
                                <option value="no" selected>Não</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tempo">Tempo de ataque: </label>
                            <select class="form-control" id="tempo" name="tempo">
                                <option value="30s" selected>30s</option>
                                <option value="1m">1m</option>
                                <option value="2m">2m</option>
                                <option value="3m">3m</option>
                                <option value="4m">4m</option>
                                <option value="5m">5m</option>
                                <option value="6m">6m</option>
                                <option value="7m">7m</option>
                                <option value="8m">8m</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div id="analysisData"></div>
                        </div>
                    </div>
                    <input type="hidden" id="num_forms" value="0" />
                    <input type="hidden" id="params_post" name="params_post" value="" />
                    <input type="hidden" id="action_url" name="action_url" value="" />
                </form>
            </div>
            <div id="no_forms_message" style="visibility: hidden;">
                <p>Aplicação não suportada. Não foram encontrados formulários.</p>
            </div>
            <div class="card-footer bg-white">
                <button type="submit"  data-toggle="modal" data-target="#modal_attack" class="btn btn-danger">{{ __('Atacar') }}</button>
            </div>
        @else
            <div class="card-header bg-white font-weight-bold">
                {{ __('Não há aplicações/analises cadastradas para a empresa ')}} <b> {{$company->name}} </b>
            </div>
            <div class="card-body">
                <p>
                    Não existem aplicações cadastradas ou nenhuma aplicação foi analisada. Para realizar um ataque é necessário que exista ao menos uma aplicação cadastrada e analisada.
                </p>
                <a href="{{ route('analysis.cadastrof', absolute: false) }}" class="btn btn-primary">{{ __('Cadastrar Analise') }}</a>
                <a href="{{ route('aplicacoes.cadastrarf', absolute: false) }}" class="btn btn-primary">{{ __('Cadastrar Aplicação') }}</a>
            </div>
        @endif
    </div>
    <div id="loading" class="overlay d-flex justify-content-center align-items-center">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p>Carregando...</p>
        </div>
    </div>
    <!-- div id="loading" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i> Loading...
    </div -->

    <x-atomicsec-modal 
        modal_id="modal_attack" 
        titulo="Ataque HTTP Keep-Alive" 
        confirm="true" 
        texto="Tem certeza que deseja atacar essa aplição ?" 
        texto_confirmacao="Atacar" 
        texto_cancelar="Cancelar"
    ></x-atomicsec-modal>
    <x-slot name="extra_end_tag">
        <script src="/js/attacks/slow_post.js"></script>
    </x-slot>
    <x-slot name="extra_script">
        $('#modal_attack').on('show.bs.modal', function (event) {

            // Update the modal's content.
            var modal = $(this);
            modal.find('.modal-title').text(modal.find('.modal-title').text() + ' ( Aplicação ' + document.getElementById('aplicacao').options[document.getElementById('aplicacao').selectedIndex].text + ' )');

            // Pass the item to the delete function
            $('#modal_attack_confirm').off('click').on('click', function () {
                document.getElementById('atacar_form').submit();
            });
        });


/*
        $(document).ready(function() {
            // Função para buscar e exibir a última análise com base no ID da aplicação
            function fetchLatestAnalysis(applicationId) {
                $.ajax({
                    url: '/ajax/aplicacoes/' + applicationId + '/ultima-analise',
                    type: 'GET',
                    success: function(response) {
                        // Manipula os dados recebidos e exibe no elemento desejado
                        $('#analysisData').html(`
                            <p>Status: ${response.status}</p>
                            <p>Análise: ${response.analysis}</p>
                            <p>Log: ${response.log}</p>
                            <p>Data de criação: ${response.created_at}</p>
                        `);
                    },
                    complete: function() {
                        // Ocultar o loading após a resposta
                        $('#loading').hide();
                    },
                    error: function(xhr) {
                        // Também pode ocultar o loading em caso de erro
                        $('#loading').hide();
                        $('#analysisData').html(`<p style="color: red;">${xhr.responseJSON.error}</p>`);
                    }
                });
            }
            // Mostrar o loading antes de enviar a requisição
            $('#loading').show();

            // Chama a função quando a página carregar para o valor inicial do select
            var initialApplicationId = $('#aplicacao').val();
            fetchLatestAnalysis(initialApplicationId);

            // Adiciona o evento 'change' para quando o valor da seleção mudar
            $('#aplicacao').on('change', function() {
                // Mostrar o loading antes de enviar a requisição
                $('#loading').show();
                var selectedApplicationId = $(this).val();
                fetchLatestAnalysis(selectedApplicationId);
            });
        });*/

    </x-slot>
</x-dashboard-layout>