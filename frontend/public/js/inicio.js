async function carregarResumo() {
    try {
        const itens = await apiFetch('/itens');

        document.getElementById('stat-total-itens').textContent = itens.length;
        document.getElementById('stat-estoque-total').textContent = itens.reduce(
            (soma, item) => soma + item.estoque,
            0,
        );
        document.getElementById('stat-categorias').textContent = new Set(
            itens.map((item) => item.categoria),
        ).size;
    } catch (erro) {
        mostrarToast(erro instanceof ErroApi ? erro.erros : ['Falha ao carregar o resumo.'], 'erro');
    }
}

carregarResumo();
