function iniciarTelaMovimentacao({
  endpointRegistro,
  endpointConsultaPorItem,
}) {
  const formulario = document.getElementById("form-movimentacao");
  const corpoLinhas = document.getElementById("linhas-itens");
  const botaoAdicionarLinha = document.getElementById("btn-add-linha");
  const caixaMensagem = document.getElementById("mensagem");
  const selectConsultaItem = document.getElementById("select-consulta-item");
  const botaoConsultar = document.getElementById("btn-consultar");
  const corpoConsulta = document.getElementById("linhas-consulta");

  let itensDisponiveis = [];

  function preencherSelectItens(select) {
    const selecionadoAtual = select.value;

    select.innerHTML = itensDisponiveis
      .map(
        (item) =>
          `<option value="${item.item_id}">${item.descricao} (estoque: ${item.estoque})</option>`,
      )
      .join("");

    if (selecionadoAtual) {
      select.value = selecionadoAtual;
    }
  }

  //TODO: melhorar carregamento de itens
  async function carregarItens() {
    itensDisponiveis = await apiFetch("/itens");

    document.querySelectorAll(".select-item").forEach(preencherSelectItens);
    preencherSelectItens(selectConsultaItem);
  }

  function atualizarBotoesRemover() {
    const linhas = corpoLinhas.querySelectorAll("tr");
    linhas.forEach((linha) => {
      linha.querySelector(".btn-remover-linha").disabled = linhas.length === 1;
    });
  }

  function criarLinha() {
    const linha = document.createElement("tr");
    linha.innerHTML = `
            <td><select class="select-item"></select></td>
            <td><input type="number" class="input-quantidade" min="1" step="1" value="1" required></td>
            <td><button type="button" class="btn-remover-linha">Remover</button></td>
        `;

    corpoLinhas.appendChild(linha);
    preencherSelectItens(linha.querySelector(".select-item"));
    atualizarBotoesRemover();
  }

  corpoLinhas.addEventListener("click", (evento) => {
    if (evento.target.classList.contains("btn-remover-linha")) {
      evento.target.closest("tr").remove();
      atualizarBotoesRemover();
    }
  });

  botaoAdicionarLinha.addEventListener("click", criarLinha);

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

  function coletarItensDoFormulario() {
    return Array.from(corpoLinhas.querySelectorAll("tr")).map((linha) => ({
      item_id: Number(linha.querySelector(".select-item").value),
      quantidade: Number(linha.querySelector(".input-quantidade").value),
    }));
  }

  formulario.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    limparMensagem();

    const dadosCabecalho = Object.fromEntries(
      new FormData(formulario).entries(),
    );
    const payload = { ...dadosCabecalho, itens: coletarItensDoFormulario() };

    try {
      await apiFetch(endpointRegistro, {
        method: "POST",
        body: JSON.stringify(payload),
      });

      mostrarSucesso("Registrado com sucesso.");
      formulario.reset();
      corpoLinhas.innerHTML = "";
      criarLinha();
      await carregarItens();
    } catch (erro) {
      mostrarErros(
        erro instanceof ErroApi
          ? erro.erros
          : ["Falha de comunicação com a API."],
      );
    }
  });

  botaoConsultar.addEventListener("click", async () => {
    const itemId = selectConsultaItem.value;
    if (!itemId) {
      return;
    }

    const movimentos = await apiFetch(endpointConsultaPorItem(itemId));

    corpoConsulta.innerHTML =
      movimentos.length === 0
        ? '<tr><td colspan="4">Nenhum movimento encontrado para este item.</td></tr>'
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
  });

  criarLinha();
  carregarItens();
}
