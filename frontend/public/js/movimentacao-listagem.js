function iniciarListagemMovimentacao({
  endpointListarTodos,
  endpointConsultaPorItem,
}) {
  const selectConsultaItem = document.getElementById("select-consulta-item");
  const botaoConsultar = document.getElementById("btn-consultar");
  const corpoConsulta = document.getElementById("linhas-consulta");

  function renderizarEstadoVazio(mensagem) {
    corpoConsulta.innerHTML = `<tr><td colspan="4" class="estado-vazio">${mensagem}</td></tr>`;
  }

  function renderizarMovimentos(movimentos) {
    if (movimentos.length === 0) {
      renderizarEstadoVazio("Nenhum movimento encontrado.");
      return;
    }

    corpoConsulta.innerHTML = movimentos
      .map(
        (mov) => `
            <tr>
                <td>${mov.data}</td>
                <td>${mov.documento}</td>
                <td>${mov.origem_destino}</td>
                <td>${mov.quantidade}</td>
            </tr>
        `,
      )
      .join("");
  }

  async function carregarItensParaFiltro() {
    const itens = await apiFetch("/itens");

    selectConsultaItem.innerHTML =
      '<option value="">Todos os itens</option>' +
      itens
        .map(
          (item) =>
            `<option value="${item.item_id}">${item.descricao} (estoque: ${item.estoque})</option>`,
        )
        .join("");
  }

  async function consultar() {
    const itemId = selectConsultaItem.value;

    try {
      const movimentos = itemId
        ? await apiFetch(endpointConsultaPorItem(itemId))
        : await apiFetch(endpointListarTodos);

      renderizarMovimentos(movimentos);
    } catch (erro) {
      mostrarToast(
        erro instanceof ErroApi
          ? erro.erros
          : ["Falha ao consultar o histórico."],
        "erro",
      );
    }
  }

  botaoConsultar.addEventListener("click", consultar);

  carregarItensParaFiltro().then(consultar);
}
