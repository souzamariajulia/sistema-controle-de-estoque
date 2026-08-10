const PAGINAS_MENU = [
    { href: 'index.html', rotulo: 'Início', icone: 'assets/house.svg' },
    { href: 'itens.html', rotulo: 'Itens', icone: 'assets/box.svg', paginasRelacionadas: ['item-novo.html', 'item-detalhes.html'] },
    { href: 'entradas.html', rotulo: 'Entradas', icone: 'assets/download.svg', paginasRelacionadas: ['entrada-nova.html'] },
    { href: 'saidas.html', rotulo: 'Saídas', icone: 'assets/upload.svg', paginasRelacionadas: ['saida-nova.html'] },
    { href: 'relatorio.html', rotulo: 'Relatório', icone: 'assets/chart-column-increasing.svg' },
];

async function carregarSvg(caminho) {
    const resposta = await fetch(caminho);
    return resposta.text();
}

async function montarCabecalho() {
    const paginaAtual = window.location.pathname.split('/').pop() || 'index.html';

    const paginasComIcone = await Promise.all(
        PAGINAS_MENU.map(async (pagina) => ({
            ...pagina,
            svg: await carregarSvg(pagina.icone),
        })),
    );

    const links = paginasComIcone
        .map((pagina) => {
            const ativo = pagina.href === paginaAtual
                || (pagina.paginasRelacionadas ?? []).includes(paginaAtual);
            const classeAtiva = ativo ? ' class="ativo"' : '';
            return `<a href="${pagina.href}"${classeAtiva}>${pagina.svg}<span>${pagina.rotulo}</span></a>`;
        })
        .join('');

    document.querySelector('header').innerHTML = `
        <a class="marca" href="index.html">
            <img class="marca-icone" src="img/icone-alucom.png" alt="ALUCOM" />
            <span class="marca-texto">
                <strong>ALUCOM</strong>
                <span>Controle de Estoque</span>
            </span>
        </a>
        <nav>${links}</nav>
    `;
}

function montarRodape() {
    const rodape = document.createElement('footer');
    rodape.innerHTML = '<p>Sistema de Controle de Estoque</p>';
    document.body.appendChild(rodape);
}

montarCabecalho();
montarRodape();
