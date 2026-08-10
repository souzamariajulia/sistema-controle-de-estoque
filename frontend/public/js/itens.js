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
                    <a class="link-detalhes" href="item-detalhes.html?id=${item.item_id}" title="Visualizar detalhes" aria-label="Visualizar detalhes">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
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

carregarItens();
