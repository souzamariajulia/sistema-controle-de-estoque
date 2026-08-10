const caixaMensagem = document.getElementById("mensagem");
const blocoDetalhes = document.getElementById("detalhes-item");

function mostrarErro(mensagemTexto) {
  caixaMensagem.className = "mensagem erro";
  caixaMensagem.textContent = mensagemTexto;
  caixaMensagem.hidden = false;
}

function renderizarLinhasMovimento(corpo, movimentos) {
  corpo.innerHTML =
    movimentos.length === 0
      ? '<tr><td colspan="4" class="estado-vazio">Nenhum movimento encontrado.</td></tr>'
      : movimentos
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

function iniciarAbas() {
  const botoes = document.querySelectorAll(".aba-botao");

  botoes.forEach((botao) => {
    botao.addEventListener("click", () => {
      botoes.forEach((b) => b.classList.remove("ativa"));
      botao.classList.add("ativa");

      document.querySelectorAll(".aba-painel").forEach((painel) => {
        painel.hidden = painel.id !== `painel-${botao.dataset.aba}`;
      });
    });
  });
}

async function iniciar() {
  const itemId = new URLSearchParams(window.location.search).get("id");

  if (!itemId) {
    mostrarErro("Nenhum item informado na URL.");
    return;
  }

  let item;
  try {
    item = await apiFetch(`/itens/${itemId}`);
  } catch (erro) {
    mostrarErro(
      erro instanceof ErroApi
        ? erro.erros.join("; ")
        : "Falha ao carregar o item.",
    );
    return;
  }

  document.getElementById("titulo-item").textContent = item.descricao;
  document.getElementById("info-categoria").textContent = item.categoria;
  document.getElementById("info-subcategoria").textContent = item.subcategoria;
  document.getElementById("info-cadastrado-por").textContent =
    item.cadastrado_por;
  document.getElementById("info-estoque").textContent = item.estoque;
  blocoDetalhes.hidden = false;

  iniciarAbas();

  const entradas = await apiFetch(`/itens/${itemId}/entradas`);
  const saidas = await apiFetch(`/itens/${itemId}/saidas`);

  renderizarLinhasMovimento(
    document.getElementById("linhas-entradas"),
    entradas,
  );
  renderizarLinhasMovimento(document.getElementById("linhas-saidas"), saidas);
}

iniciar();
