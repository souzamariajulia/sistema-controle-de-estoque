function garantirContainerToast() {
    let container = document.getElementById('toast-container');

    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    return container;
}

/**
 * @param {string|string[]} mensagens
 * @param {'sucesso'|'erro'} tipo
 */
function mostrarToast(mensagens, tipo = 'sucesso') {
    const lista = Array.isArray(mensagens) ? mensagens : [mensagens];
    const container = garantirContainerToast();

    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.innerHTML = lista.length > 1
        ? `<ul>${lista.map((mensagem) => `<li>${mensagem}</li>`).join('')}</ul>`
        : `<span>${lista[0]}</span>`;

    const botaoFechar = document.createElement('button');
    botaoFechar.type = 'button';
    botaoFechar.className = 'toast-fechar';
    botaoFechar.setAttribute('aria-label', 'Fechar');
    botaoFechar.textContent = '×';
    botaoFechar.addEventListener('click', () => toast.remove());

    toast.appendChild(botaoFechar);
    container.appendChild(toast);

    setTimeout(() => toast.remove(), 5000);
}
