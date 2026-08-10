const selectItem = document.getElementById('filtro-item');
const inputDataInicio = document.getElementById('filtro-data-inicio');
const inputDataFim = document.getElementById('filtro-data-fim');
const botaoGerar = document.getElementById('btn-gerar');
const botaoExportar = document.getElementById('btn-exportar');
const botaoImprimir = document.getElementById('btn-imprimir');
const corpoTabela = document.getElementById('linhas-relatorio');

function construirQueryString() {
    const parametros = new URLSearchParams();

    if (selectItem.value) {
        parametros.set('item_id', selectItem.value);
    }
    if (inputDataInicio.value) {
        parametros.set('data_inicio', inputDataInicio.value);
    }
    if (inputDataFim.value) {
        parametros.set('data_fim', inputDataFim.value);
    }

    return parametros.toString();
}

function renderizarRelatorio(linhas) {
    corpoTabela.innerHTML = linhas.length === 0
        ? '<tr><td colspan="8" class="estado-vazio">Nenhum item encontrado para os filtros selecionados.</td></tr>'
        : linhas.map((linha) => `
            <tr>
                <td class="celula-titulo">${linha.descricao}</td>
                <td><span class="badge badge-categoria">${linha.categoria}</span></td>
                <td><span class="badge badge-neutro">${linha.subcategoria}</span></td>
                <td>${linha.cadastrado_por}</td>
                <td>${linha.estoque}</td>
                <td>${linha.total_entradas}</td>
                <td>${linha.total_saidas}</td>
                <td>${linha.saldo_movimentado}</td>
            </tr>
        `).join('');
}

async function carregarItensParaFiltro() {
    const itens = await apiFetch('/itens');

    for (const item of itens) {
        const opcao = document.createElement('option');
        opcao.value = item.item_id;
        opcao.textContent = item.descricao;
        selectItem.appendChild(opcao);
    }
}

async function gerarRelatorio() {
    try {
        renderizarRelatorio(await apiFetch(`/relatorios/estoque?${construirQueryString()}`));
    } catch (erro) {
        mostrarToast(erro instanceof ErroApi ? erro.erros : ['Falha ao gerar o relatório.'], 'erro');
    }
}

botaoGerar.addEventListener('click', gerarRelatorio);

botaoExportar.addEventListener('click', () => {
    const query = construirQueryString();
    const separador = query ? '&' : '';
    window.location.href = `${API_BASE_URL}/relatorios/estoque?${query}${separador}formato=xlsx`;
});

botaoImprimir.addEventListener('click', () => window.print());

carregarItensParaFiltro();
gerarRelatorio();
