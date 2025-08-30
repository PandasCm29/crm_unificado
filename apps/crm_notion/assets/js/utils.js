function escapeHTML(str) {
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function stripTags(html) {
  const div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
}

function escapeRegex(text) {
  return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

export function mostrarTx(valores, tipo = 1, highlightWords = []) {
  const tiposInicio = {
    th: 'px-4 py-3 text-center text-xs font-medium uppercase tracking-wider whitespace-nowrap',
    td: 'px-4 py-1 text-center whitespace-nowrap'
  };

  const tag = tipo === 0 ? 'th' : 'td';
  const claseBase = tiposInicio[tag];
  let html = '';

  valores.forEach(valor => {
    valor = String(valor);
    const contieneHTML = valor.includes('<div') || valor.includes('<span');
    const contieneDiv = contieneHTML && valor.includes('<div');
    const textoPlano = stripTags(valor); // solo una vez aquí

    let valorMostrar = contieneHTML ? valor : escapeHTML(valor);

    // Aplicar resaltado si no contiene HTML
    if (Array.isArray(highlightWords) && highlightWords.length && !contieneHTML) {
      const textoConEscape = escapeHTML(valor);

      // Combina todas las palabras en una sola expresión regular
      const regex = new RegExp(`(${highlightWords.map(escapeRegex).join('|')})`, 'gi');

      valorMostrar = textoConEscape.replace(
        regex,
        '<span class="bg-yellow-200 font-bold">$1</span>'
      );
    }


    const esLargo = tipo === 1 && textoPlano.length > 20 && !contieneDiv;
    const claseExtra = esLargo ? ' max-w-[200px] truncate' : '';
    const titleAttr = esLargo ? ` title="${escapeHTML(textoPlano)}"` : '';

    html += `<${tag} class="${claseBase}${claseExtra}"${titleAttr}>${valorMostrar}</${tag}>`;
  });

  return html;
}

export function vaciarForm(form){
  if (!form) return;
  // Limpiar todos los campos de entrada, textarea y select
  // Array.from(form.elements).forEach(el => {
  //     if (['INPUT','TEXTAREA','SELECT'].includes(el.tagName) && !['button','submit','reset'].includes(el.type)) {
  //         el.value = '';
  //     }
  // });
  form.reset();
  // Foco en el primer campo de entrada
  const firstInput = form?.querySelector('input, textarea, select');
  firstInput?.focus();
}

export function switchModal(modal, open=false, vaciarForm_=false) {
  if (!modal) {
    console.warn("No modal element provided");
    return;
  }
  if (vaciarForm_) {
    const form = modal.querySelector('form');
    vaciarForm(form)
  };
  modal.classList.toggle('hidden', !open);
  document.body.style.overflow = !open ? '':'hidden';
}