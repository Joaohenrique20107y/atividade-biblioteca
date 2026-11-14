async function apiFetch(url, options = {}) {
  const config = { ...options };
  config.headers = { ...(options.headers || {}) };
  if (config.body && typeof config.body !== 'string') {
    config.headers['Content-Type'] = 'application/json';
    config.body = JSON.stringify(config.body);
  }

  let response;
  try {
    response = await fetch(url, config);
  } catch (error) {
    throw new Error('Não foi possível conectar ao servidor. Verifique se o PHP está em execução.');
  }

  const data = await response.json().catch(() => ({}));
  if (!response.ok || (data && data.erro)) {
    throw new Error(data.erro || 'Ocorreu um erro ao processar a solicitação.');
  }
  return data;
}

function mostrarMensagem(texto, tipo = 'success') {
  const elemento = document.getElementById('mensagem');
  if (!elemento) return;
  elemento.textContent = texto;
  elemento.className = `message ${tipo}`;
  elemento.hidden = false;
  window.clearTimeout(mostrarMensagem.timer);
  mostrarMensagem.timer = window.setTimeout(() => { elemento.hidden = true; }, 5000);
}

function escaparHTML(valor) {
  return String(valor ?? '').replace(/[&<>"']/g, caractere => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  })[caractere]);
}

function formatarData(data) {
  if (!data) return '-';
  const partes = String(data).slice(0, 10).split('-');
  return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : data;
}
