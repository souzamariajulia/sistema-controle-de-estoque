function iniciarFormularioMovimentacao({ endpointRegistro, paginaListagem }) {
    const formulario = document.getElementById('form-movimentacao');
    const inputData = formulario.querySelector('[name="data"]');
    const corpoLinhas = document.getElementById('linhas-itens');
    const botaoAdicionarLinha = document.getElementById('btn-add-linha');

    inputData.max = new Date().toISOString().slice(0, 10);

    let itensDisponiveis = [];

    function preencherSelectItens(select) {
        const selecionadoAtual = select.value;

        select.innerHTML = itensDisponiveis
            .map((item) => `<option value="${item.item_id}">${item.descricao} (estoque: ${item.estoque})</option>`)
            .join('');

        if (selecionadoAtual) {
            select.value = selecionadoAtual;
        }
    }

    async function carregarItens() {
        itensDisponiveis = await apiFetch('/itens');
        document.querySelectorAll('.select-item').forEach(preencherSelectItens);
    }

    function atualizarBotoesRemover() {
        const linhas = corpoLinhas.querySelectorAll('tr');
        linhas.forEach((linha) => {
            linha.querySelector('.btn-remover-linha').disabled = linhas.length === 1;
        });
    }

    function criarLinha() {
        const linha = document.createElement('tr');
        linha.innerHTML = `
            <td><select class="select-item"></select></td>
            <td><input type="number" class="input-quantidade" min="1" step="1" value="1" required></td>
            <td><button type="button" class="btn-remover-linha">Remover</button></td>
        `;

        corpoLinhas.appendChild(linha);
        preencherSelectItens(linha.querySelector('.select-item'));

        const inputQuantidade = linha.querySelector('.input-quantidade');
        inputQuantidade.addEventListener('input', () => {
            inputQuantidade.value = inputQuantidade.value.replace(/\D/g, '');
        });

        atualizarBotoesRemover();
    }

    corpoLinhas.addEventListener('click', (evento) => {
        if (evento.target.classList.contains('btn-remover-linha')) {
            evento.target.closest('tr').remove();
            atualizarBotoesRemover();
        }
    });

    botaoAdicionarLinha.addEventListener('click', criarLinha);

    function coletarItensDoFormulario() {
        return Array.from(corpoLinhas.querySelectorAll('tr')).map((linha) => ({
            item_id: Number(linha.querySelector('.select-item').value),
            quantidade: Number(linha.querySelector('.input-quantidade').value),
        }));
    }

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const dadosCabecalho = Object.fromEntries(new FormData(formulario).entries());
        const payload = { ...dadosCabecalho, itens: coletarItensDoFormulario() };

        try {
            await apiFetch(endpointRegistro, {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            mostrarToast('Registrado com sucesso.', 'sucesso');
            window.location.href = paginaListagem;
        } catch (erro) {
            mostrarToast(erro instanceof ErroApi ? erro.erros : ['Falha de comunicação com a API.'], 'erro');
        }
    });

    criarLinha();
    carregarItens();
}
