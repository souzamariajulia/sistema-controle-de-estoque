const formulario = document.getElementById("form-cadastro-item");
const caixaMensagem = document.getElementById("mensagem-cadastro");
const selectSubcategoria = document.getElementById("select-subcategoria");
const corpoItens = document.getElementById("linhas-itens");
const inputBusca = document.getElementById("input-busca");
const botaoBuscar = document.getElementById("btn-buscar");
const botaoLimparBusca = document.getElementById("btn-limpar-busca");

function limparMensagem() {
  caixaMensagem.hidden = true;
  caixaMensagem.className = "mensagem";
  caixaMensagem.innerHTML = "";
}

function mostrarErros(erros) {
  caixaMensagem.className = "mensagem erro";
  caixaMensagem.innerHTML = `<ul>${erros.map((erro) => `<li>${erro}</li>`).join("")}</ul>`;
  caixaMensagem.hidden = false;
}

function mostrarSucesso(mensagemTexto) {
  caixaMensagem.className = "mensagem sucesso";
  caixaMensagem.textContent = mensagemTexto;
  caixaMensagem.hidden = false;
}

async function carregarSubcategorias() {
  const subcategorias = await apiFetch("/subcategorias");

  selectSubcategoria.innerHTML = subcategorias
    .map(
      (sub) =>
        `<option value="${sub.id}">${sub.categoria} &rsaquo; ${sub.nome}</option>`,
    )
    .join("");
}

function renderizarItens(itens) {
  corpoItens.innerHTML =
    itens.length === 0
      ? '<tr><td colspan="5">Nenhum item encontrado.</td></tr>'
      : itens
          .map(
            (item) => `
            <tr>
                <td>${item.descricao}</td>
                <td>${item.categoria}</td>
                <td>${item.subcategoria}</td>
                <td>${item.estoque}</td>
                <td><a class="link-tabela" href="item-detalhes.html?id=${item.item_id}">Ver detalhes</a></td>
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

botaoLimparBusca.addEventListener("click", () => {
  inputBusca.value = "";
  carregarItens();
});

formulario.addEventListener("submit", async (evento) => {
  evento.preventDefault();
  limparMensagem();

  const dados = Object.fromEntries(new FormData(formulario).entries());

  try {
    await apiFetch("/itens", {
      method: "POST",
      body: JSON.stringify(dados),
    });

    mostrarSucesso("Item cadastrado com sucesso.");
    formulario.reset();
    await carregarItens();
  } catch (erro) {
    mostrarErros(
      erro instanceof ErroApi
        ? erro.erros
        : ["Falha de comunicação com a API."],
    );
  }
});

carregarSubcategorias();
carregarItens();
