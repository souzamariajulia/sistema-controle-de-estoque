const corpoItens = document.getElementById("linhas-itens");
const inputBusca = document.getElementById("input-busca");
const botaoBuscar = document.getElementById("btn-buscar");

function renderizarItens(itens) {
  corpoItens.innerHTML =
    itens.length === 0
      ? '<tr><td colspan="6" class="estado-vazio">Nenhum item encontrado.</td></tr>'
      : itens
          .map(
            (item) => `
            <tr>
                <td class="celula-titulo">${item.descricao}</td>
                <td>${item.cadastrado_por}</td>
                <td><span class="badge badge-categoria">${item.categoria}</span></td>
                <td><span class="badge badge-neutro">${item.subcategoria}</span></td>
                <td>${item.estoque}</td>
                <td class="celula-acoes">
                    <button type="button" class="btn-acoes" aria-label="Ações">&#8942;</button>
                    <div class="menu-acoes" hidden>
                        <a href="item-novo.html?id=${item.item_id}">Editar</a>
                        <a href="item-detalhes.html?id=${item.item_id}">Ver detalhes</a>
                        <button type="button" class="menu-item-excluir" data-id="${item.item_id}" data-descricao="${item.descricao}">Excluir</button>
                    </div>
                </td>
            </tr>
        `,
          )
          .join("");
}

async function carregarItens(termoBusca) {
  const caminho = termoBusca
    ? `/itens?busca=${encodeURIComponent(termoBusca)}`
    : "/itens";
  renderizarItens(await apiFetch(caminho));
}

botaoBuscar.addEventListener("click", () =>
  carregarItens(inputBusca.value.trim()),
);

inputBusca.addEventListener("keydown", (evento) => {
  if (evento.key === "Enter") {
    evento.preventDefault();
    carregarItens(inputBusca.value.trim());
  }
});

function fecharTodosOsMenus() {
  document.querySelectorAll(".menu-acoes").forEach((menu) => {
    menu.hidden = true;
  });
}

async function excluirItem(id, descricao) {
  const confirmou = window.confirm(
    `Excluir o item "${descricao}"? Essa ação não pode ser desfeita.`,
  );

  if (!confirmou) {
    return;
  }

  try {
    await apiFetch(`/itens/${id}`, { method: "DELETE" });
    mostrarToast("Item excluído com sucesso.", "sucesso");
    await carregarItens(inputBusca.value.trim());
  } catch (erro) {
    mostrarToast(
      erro instanceof ErroApi ? erro.erros : ["Falha ao excluir o item."],
      "erro",
    );
  }
}

corpoItens.addEventListener("click", (evento) => {
  const botaoAcoes = evento.target.closest(".btn-acoes");
  if (botaoAcoes) {
    const menu = botaoAcoes.nextElementSibling;
    const jaAberto = !menu.hidden;
    fecharTodosOsMenus();
    menu.hidden = jaAberto;
    return;
  }

  const botaoExcluir = evento.target.closest(".menu-item-excluir");
  if (botaoExcluir) {
    fecharTodosOsMenus();
    excluirItem(botaoExcluir.dataset.id, botaoExcluir.dataset.descricao);
  }
});

document.addEventListener("click", (evento) => {
  if (!evento.target.closest(".celula-acoes")) {
    fecharTodosOsMenus();
  }
});

carregarItens();
