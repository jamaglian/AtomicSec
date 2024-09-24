$(document).ready(function() {
    var storedForms = [];

    // Função para buscar e exibir a última análise com base no ID da aplicação
    function fetchLatestAnalysis(applicationId) {
        $.ajax({
            url: '/ajax/aplicacoes/' + applicationId + '/ultima-analise',
            type: 'GET',
            success: function(response) {
                // Extraindo dados dos formulários únicos
                var uniqueForms = getUniqueForms(response.analysis);

                // Exibindo os formulários
                displayForms(uniqueForms);
            },
            complete: function() {
                // Ocultar o loading após a resposta
                document.getElementById('loading').style.visibility = "hidden";
                post_load();
            },
            error: function(xhr) {
                // Ocultar o loading após a resposta
                document.getElementById('loading').style.visibility = "hidden";
                $('#analysisData').html(`<p style="color: red;">${xhr.responseJSON.error}</p>`);
                post_load();
            }
        });
    }

    // Função para extrair e garantir a unicidade dos formulários
    function getUniqueForms(analysisData) {
        var data = JSON.parse(analysisData);
        var forms = [];

        // Itera sobre cada URL e seus dados de formulário
        for (var url in data.serverRequestTimeMap) {
            if (data.serverRequestTimeMap.hasOwnProperty(url)) {
                var urlData = data.serverRequestTimeMap[url];
                
                if (urlData.forms && urlData.forms.length > 0) {
                    urlData.forms.forEach(function(form) {
                        // Verifica se o formulário já não está na lista de formulários
                        var exists = forms.some(function(f) {
                            return JSON.stringify(f.params) === JSON.stringify(form.params);
                        });
                        
                        if (!exists) {
                            var numForms = document.getElementById("num_forms");
                            
                            // Converte o valor atual para número e incrementa
                            var valorAtual = parseInt(numForms.value);
                            numForms.value = valorAtual + 1;
                            forms.push(form);
                        }
                    });
                }
            }
        }
        
        return forms;
    }

    // Função para exibir os formulários na página e criar um select box
    function displayForms(forms) {
        // Store the forms globally
        storedForms = forms;

        // Create a select box with the form URLs
        var selectOptions = forms.map(function(form, index) {
            return `<option value="${index}">${form.params.url}</option>`;
        }).join('');

        var selectHtml = `<label for="formSelect">Escolha um formulário:</label>
                        <select id="formSelect" class="form-control">${selectOptions}</select>`;

        // Append the select box and a container to display the selected form details
        var html = `<div class="form-selection">${selectHtml}</div>
                    <div id="formDetails"></div>`;

        $('#analysisData').html(html);

        // Set event listener for form selection
        $('#formSelect').on('change', function() {
            var selectedFormIndex = $(this).val();
            displayFormDetails(selectedFormIndex);
        });

        // Display details for the first form by default
        if (forms.length > 0) {
            displayFormDetails(0);
        }
    }

    // Função para exibir os detalhes do formulário selecionado com input e checkbox (AUTO)
    function displayFormDetails(index) {
        var form = storedForms[index];
        var formData = form.params.formData;

        // Generate HTML for form fields with input and checkbox (AUTO)
        var fields = Object.keys(formData).map(function(key) {
            var field = formData[key];
            var inputId = `input_${key}`;
            var checkboxId = `auto_${key}`;
            
            return `<div class="form-group row">
                        <label for="${inputId}" class="col-sm-4 col-form-label">${key}:</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="${inputId}" value="${field.value}">
                        </div>
                        <div class="col-sm-2">
                            <input type="checkbox" id="${checkboxId}" class="form-check-input"> AUTO
                        </div>
                    </div>`;
        }).join('');

        // Display the form details
        var html = `<div class="form-container">
                        <h3>Formulário para ${form.params.url}</h3>
                        ${fields}
                    </div>`;
        document.getElementById('action_url').value = form.params.url;
        $('#formDetails').html(html);
        
        // Adicionar eventos de mudança nas inputs e checkboxes após exibir os detalhes
        addInputChangeListeners(formData);
        
        // Atualizar o hidden de params_post quando os detalhes forem exibidos
        updateParamsPost();
    }

    // Função para adicionar listeners de mudança nas inputs e checkboxes
    function addInputChangeListeners(formData) {
        Object.keys(formData).forEach(function(key) {
            var inputId = `input_${key}`;
            var checkboxId = `auto_${key}`;

            // Adicionar listeners para mudanças nos campos
            $(`#${inputId}, #${checkboxId}`).on('change', function() {
                updateParamsPost();
            });
        });
    }

    // Função para atualizar o campo hidden de params_post
    function updateParamsPost() {
        var params = [];

        storedForms.forEach(function(form, index) {
            var formData = form.params.formData;
            if(index == $('#formSelect').val()){
                Object.keys(formData).forEach(function(key) {
                    var inputId = `input_${key}`;
                    var checkboxId = `auto_${key}`;
                    
                    var inputValue = $(`#${inputId}`).val();
                    var isAuto = $(`#${checkboxId}`).is(':checked');

                    // Se o checkbox "AUTO" estiver selecionado, definir valor como "AUTO"
                    if (isAuto) {
                        $(`#${inputId}`).prop('disabled', true);
                        params.push(`${key}=AUTO`);
                    } else {
                        $(`#${inputId}`).prop('disabled', false);
                        if(inputValue !== undefined){
                            params.push(`${key}=${encodeURIComponent(inputValue)}`);
                        }else{
                            params.push(`${key}=`);
                        }
                    }
                });
            }
        });

        // Atualizar o campo hidden com os parâmetros concatenados
        $('#params_post').val(params.join('&'));
    }

    function post_load(){
        var numForms = document.getElementById("num_forms");
        var valorAtual = parseInt(numForms.value);
        if(valorAtual > 0){
            document.getElementById('no_forms_message').style.visibility = "hidden";
            document.getElementById('resto_form').style.visibility = "visible";
        }else{
            document.getElementById('no_forms_message').style.visibility = "visible";
        }
    }

    document.getElementById('loading').style.visibility = "visible";
    document.getElementById("num_forms").value = 0;

    // Chama a função quando a página carregar para o valor inicial do select
    var initialApplicationId = $('#aplicacao').val();
    fetchLatestAnalysis(initialApplicationId);

    // Adiciona o evento 'change' para quando o valor da seleção mudar
    $('#aplicacao').on('change', function() {
        document.getElementById("num_forms").value = 0;
        document.getElementById('loading').style.visibility = "visible";
        var selectedApplicationId = $(this).val();
        fetchLatestAnalysis(selectedApplicationId);
    });
});
