const formulario = document.getElementById("form-cadastro-item");
const inputEstoque = formulario.querySelector('[name="estoque"]');
const selectSubcategoria = document.getElementById("select-subcategoria");

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

formulario.addEventListener("submit", async (evento) => {
  evento.preventDefault();

  const dados = Object.fromEntries(new FormData(formulario).entries());

  try {
    await apiFetch("/itens", {
      method: "POST",
      body: JSON.stringify(dados),
    });

    mostrarToast("Item cadastrado com sucesso.", "sucesso");
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

carregarSubcategorias();
