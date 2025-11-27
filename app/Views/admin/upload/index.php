<?php
/**
 * View: Upload de CSV
 */
$title = 'Upload de CSV';
$currentPage = 'upload';
$pageTitle = 'Upload de CSV';
ob_start();
?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Upload de CSV</h2>
        <p class="text-sm text-gray-600">Faça upload de arquivos CSV para importar CPFs e contratos</p>
    </div>
</div>

<div class="bg-white shadow rounded-lg p-6">
    <div class="mb-4">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Instruções</h3>
        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
            <li>O arquivo deve estar no formato CSV (separado por vírgula ou ponto e vírgula)</li>
            <li>O sistema identificará automaticamente a imobiliária pelo campo "empresa_fiscal"</li>
            <li>Colunas obrigatórias: <strong>inquilino_doc</strong> (CPF/CNPJ), <strong>contrato</strong>, <strong>empresa_fiscal</strong></li>
            <li>Colunas opcionais: inquilino_nome, ImoFinalidade, cidade, estado, bairro, CEP, endereco, numero, complemento, unidade</li>
        </ul>
    </div>

    <form id="form-upload-csv" enctype="multipart/form-data" class="mt-6">
        <div class="mb-4">
            <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                Selecione um ou mais arquivos CSV
            </label>
            <input type="file" name="csv_file[]" id="csv_file" accept=".csv" multiple
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
            <p class="mt-1 text-xs text-gray-500">Você pode selecionar múltiplos arquivos de uma vez (Ctrl+Click ou Cmd+Click)</p>
        </div>

        <div id="arquivos-selecionados" class="mb-4 hidden">
            <p class="text-sm font-medium text-gray-700 mb-2">Arquivos selecionados:</p>
            <ul id="lista-arquivos" class="text-sm text-gray-600 space-y-1"></ul>
        </div>

        <div class="flex items-center space-x-3">
            <button type="submit" id="upload-csv-button" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-upload mr-2"></i>
                Enviar Arquivos
            </button>
        </div>
    </form>

    <div id="upload-csv-result" class="mt-6 hidden"></div>
</div>

<script>
// Mostrar arquivos selecionados
document.getElementById('csv_file').addEventListener('change', function(e) {
    const arquivosSelecionados = document.getElementById('arquivos-selecionados');
    const listaArquivos = document.getElementById('lista-arquivos');
    
    if (this.files && this.files.length > 0) {
        arquivosSelecionados.classList.remove('hidden');
        listaArquivos.innerHTML = '';
        
        Array.from(this.files).forEach((file, index) => {
            const li = document.createElement('li');
            const tamanhoMB = (file.size / (1024 * 1024)).toFixed(2);
            li.innerHTML = `<i class="fas fa-file-csv mr-2 text-blue-600"></i>${file.name} <span class="text-gray-400">(${tamanhoMB} MB)</span>`;
            listaArquivos.appendChild(li);
        });
    } else {
        arquivosSelecionados.classList.add('hidden');
    }
});

document.getElementById('form-upload-csv').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const fileInput = document.getElementById('csv_file');
    const button = document.getElementById('upload-csv-button');
    const result = document.getElementById('upload-csv-result');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Por favor, selecione pelo menos um arquivo CSV');
        return;
    }
    
    const formData = new FormData();
    
    // Adicionar todos os arquivos
    Array.from(fileInput.files).forEach((file, index) => {
        formData.append(`csv_file[]`, file);
    });
    
    const originalText = button.innerHTML;
    const totalArquivos = fileInput.files.length;
    button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Enviando ${totalArquivos} arquivo(s)...`;
    button.disabled = true;
    result.classList.add('hidden');
    
    fetch('<?= url('admin/upload/processar') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        result.classList.remove('hidden');
        
        if (data.success) {
            let html = `<div class="p-4 rounded-md bg-green-50 border border-green-200">
                <p class="font-medium text-green-800 mb-2">${data.message}</p>`;
            
            // Mostrar resumo por arquivo se houver múltiplos
            if (data.arquivos && data.arquivos.length > 0) {
                html += `<div class="mt-3 space-y-2">`;
                data.arquivos.forEach(arquivo => {
                    const statusClass = arquivo.erros > 0 ? 'text-yellow-700' : 'text-green-700';
                    html += `<div class="text-sm ${statusClass}">
                        <i class="fas fa-file-csv mr-1"></i>
                        <strong>${arquivo.nome}:</strong> ${arquivo.sucessos} sucesso(s), ${arquivo.erros} erro(s)
                    </div>`;
                });
                html += `</div>`;
            }
            
            if (data.erros > 0 && data.detalhes_erros && data.detalhes_erros.length > 0) {
                html += `<details class="mt-3">
                    <summary class="text-sm text-green-700 cursor-pointer">Ver detalhes dos erros (${data.erros})</summary>
                    <ul class="mt-2 text-sm text-green-600 list-disc list-inside space-y-1 max-h-60 overflow-y-auto">`;
                data.detalhes_erros.forEach(erro => {
                    html += `<li>${erro}</li>`;
                });
                html += `</ul></details>`;
            }
            
            html += `</div>`;
            result.innerHTML = html;
            
            // Limpar formulário se tudo foi processado com sucesso
            if (data.erros === 0) {
                form.reset();
                document.getElementById('arquivos-selecionados').classList.add('hidden');
            }
        } else {
            result.innerHTML = `<div class="p-4 rounded-md bg-red-50 border border-red-200">
                <p class="font-medium text-red-800">Erro: ${data.error || 'Erro desconhecido'}</p>
            </div>`;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        result.classList.remove('hidden');
        result.innerHTML = `<div class="p-4 rounded-md bg-red-50 border border-red-200">
            <p class="font-medium text-red-800">Erro ao enviar arquivos</p>
        </div>`;
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/Views/layouts/admin.php';
?>

