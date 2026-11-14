const formLeitor = document.getElementById('formLeitor');
const listaLeitores = document.getElementById('listaLeitores');

async function carregarLeitores() {
  listaLeitores.innerHTML = '<tr><td colspan="4" class="empty">Carregando...</td></tr>';
  try {
    const leitores = await apiFetch('api/leitores');
    if (!leitores.length) {
      listaLeitores.innerHTML = '<tr><td colspan="4" class="empty">Nenhum leitor cadastrado.</td></tr>';
      return;
    }
    listaLeitores.innerHTML = leitores.map(leitor => `
      <tr><td>${leitor.id}</td><td>${escaparHTML(leitor.nome)}</td><td>${escaparHTML(leitor.email)}</td>
      <td class="actions"><button class="button button-small button-light" data-editar="${leitor.id}">Editar</button><button class="button button-small button-danger" data-excluir="${leitor.id}">Excluir</button></td></tr>`).join('');
    listaLeitores.querySelectorAll('[data-editar]').forEach(btn => btn.addEventListener('click', () => editarLeitor(leitores.find(l => l.id == btn.dataset.editar))));
    listaLeitores.querySelectorAll('[data-excluir]').forEach(btn => btn.addEventListener('click', () => excluirLeitor(btn.dataset.excluir)));
  } catch (error) {
    listaLeitores.innerHTML = '<tr><td colspan="4" class="empty">Não foi possível carregar os leitores.</td></tr>';
    mostrarMensagem(error.message, 'error');
  }
}

function editarLeitor(leitor) {
  document.getElementById('leitorId').value = leitor.id;
  document.getElementById('nome').value = leitor.nome;
  document.getElementById('email').value = leitor.email;
  document.getElementById('formTitulo').textContent = 'Editar leitor';
  document.getElementById('btnSalvar').textContent = 'Salvar alterações';
  document.getElementById('btnCancelar').hidden = false;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function limparFormulario() {
  formLeitor.reset();
  document.getElementById('leitorId').value = '';
  document.getElementById('formTitulo').textContent = 'Cadastrar leitor';
  document.getElementById('btnSalvar').textContent = 'Salvar leitor';
  document.getElementById('btnCancelar').hidden = true;
}

async function excluirLeitor(id) {
  if (!confirm('Deseja realmente excluir este leitor?')) return;
  try {
    const resultado = await apiFetch(`api/leitores/${id}`, { method: 'DELETE' });
    mostrarMensagem(resultado.mensagem);
    carregarLeitores();
  } catch (error) { mostrarMensagem(error.message, 'error'); }
}

formLeitor.addEventListener('submit', async event => {
  event.preventDefault();
  const id = document.getElementById('leitorId').value;
  const dados = { nome: document.getElementById('nome').value, email: document.getElementById('email').value };
  try {
    const resultado = await apiFetch(id ? `api/leitores/${id}` : 'api/leitores', {
      method: id ? 'PUT' : 'POST', body: dados
    });
    mostrarMensagem(resultado.mensagem);
    limparFormulario();
    carregarLeitores();
  } catch (error) { mostrarMensagem(error.message, 'error'); }
});

document.getElementById('btnCancelar').addEventListener('click', limparFormulario);
document.getElementById('btnAtualizar').addEventListener('click', carregarLeitores);
carregarLeitores();
