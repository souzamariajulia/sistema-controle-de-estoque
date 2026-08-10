function iniciarListagemMovimentacao({ endpointConsultaPorItem }) {
    const selectConsultaItem = document.getElementById('select-consulta-item');
    const botaoConsultar = document.getElementById('btn-consultar');
    const corpoConsulta = document.getElementById('linhas-consulta');

    function renderizarEstadoVazio(mensagem) {
        corpoConsulta.innerHTML = `<tr><td colspan="4" class="estado-vazio">${mensagem}</td></tr>`;
    }

    async function carregarItensParaFiltro() {
        const itens = await apiFetch('/itens');

        selectConsultaItem.innerHTML = '<option value="">Selecione um item</option>' + itens
            .map((item) => `<option value="${item.item_id}">${item.descricao} (estoque: ${item.estoque})</option>`)
            .join('');
    }

    botaoConsultar.addEventListener('click', async () => {
        const itemId = selectConsultaItem.value;
        if (!itemId) {
            return;
        }

        try {
            const movimentos = await apiFetch(endpointConsultaPorItem(itemId));

            if (movimentos.length === 0) {
                renderizarEstadoVazio('Nenhum movimento encontrado para este item.');
                return;
            }

            corpoConsulta.innerHTML = movimentos.map((mov) => `
                <tr>
                    <td>${mov.data}</td>
                    <td>${mov.documento}</td>
                    <td>${mov.origem_destino}</td>
                    <td>${mov.quantidade}</td>
                </tr>
            `).join('');
        } catch (erro) {
            mostrarToast(erro instanceof ErroApi ? erro.erros : ['Falha ao consultar o histórico.'], 'erro');
        }
    });

    renderizarEstadoVazio('Selecione um item para consultar o histórico.');
    carregarItensParaFiltro();
}
