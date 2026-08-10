const API_BASE_URL = "http://127.0.0.1:8000/api";

class ErroApi extends Error {
  constructor(erros, status) {
    super(erros.join("; "));
    this.erros = erros;
    this.status = status;
  }
}

async function apiFetch(caminho, opcoes = {}) {
  const resposta = await fetch(`${API_BASE_URL}${caminho}`, {
    headers: { "Content-Type": "application/json" },
    ...opcoes,
  });

  const corpo = await resposta.json().catch(() => null);

  if (!resposta.ok) {
    const erros = corpo?.erros ?? [
      corpo?.erro ?? `Erro inesperado (HTTP ${resposta.status})`,
    ];
    throw new ErroApi(erros, resposta.status);
  }

  return corpo;
}
