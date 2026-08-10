const formulario = document.getElementById("form-cadastro-item");
const inputEstoque = formulario.querySelector('[name="estoque"]');
const selectSubcategoria = document.getElementById("select-subcategoria");
const tituloPagina = document.getElementById("titulo-pagina");
const breadcrumbAtual = document.getElementById("breadcrumb-atual");
const botaoSalvar = document.getElementById("btn-salvar");

const idItem = new URLSearchParams(window.location.search).get("id");
const modoEdicao = idItem !== null;

inputEstoque.addEventListener("input", () => {
  inputEstoque.value = inputEstoque.value.replace(/\D/g, "");
});

async function carregarSubcategorias() {
  const subcategorias = await apiFetch("/subcategorias");

  selectSubcategoria.innerHTML = subcategorias
    .map(
      (sub) =>
        `<option value="${sub.id}">${sub.categoria} &rsaquo; ${sub.nome}</option>`,
    )
    .join("");
}

async function iniciar() {
  await carregarSubcategorias();

  if (!modoEdicao) {
    return;
  }

  document.title = "Editar Item";
  tituloPagina.textContent = "Editar item";
  breadcrumbAtual.textContent = "Editar item";
  botaoSalvar.textContent = "Salvar alterações";

  let item;
  try {
    item = await apiFetch(`/itens/${idItem}`);
  } catch (erro) {
    mostrarToast(
      erro instanceof ErroApi ? erro.erros : ["Falha ao carregar o item."],
      "erro",
    );
    return;
  }

  formulario.querySelector('[name="descricao"]').value = item.descricao;
  formulario.querySelector('[name="subcategoria_id"]').value =
    item.subcategoria_id;
  formulario.querySelector('[name="cadastrado_por"]').value =
    item.cadastrado_por;
  formulario.querySelector('[name="estoque"]').value = item.estoque;
}

formulario.addEventListener("submit", async (evento) => {
  evento.preventDefault();

  const dados = Object.fromEntries(new FormData(formulario).entries());

  try {
    await apiFetch(modoEdicao ? `/itens/${idItem}` : "/itens", {
      method: modoEdicao ? "PUT" : "POST",
      body: JSON.stringify(dados),
    });

    mostrarToast(
      modoEdicao ? "Item atualizado com sucesso." : "Item cadastrado com sucesso.",
      "sucesso",
    );
    window.location.href = "itens.html";
  } catch (erro) {
    mostrarToast(
      erro instanceof ErroApi
        ? erro.erros
        : ["Falha de comunicação com a API."],
      "erro",
    );
  }
});

iniciar();
