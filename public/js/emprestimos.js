const formEmprestimo = document.getElementById('formEmprestimo');
const listaEmprestimos = document.getElementById('listaEmprestimos');
const selectLivro = document.getElementById('livro_id');
const selectLeitor = document.getElementById('leitor_id');

async function carregarOpcoes() {
  try {
    const [livros, leitores] = await Promise.all([apiFetch('api/livros'), apiFetch('api/leitores')]);
    selectLivro.innerHTML = '<option value="">Selecione um livro</option>' + livros
      .filter(livro => Number(livro.disponiveis) > 0)
      .map(livro => `<option value="${livro.id}">${escaparHTML(livro.titulo)} (${livro.disponiveis} disponível(is))</option>`).join('');
    selectLeitor.innerHTML = '<option value="">Selecione um leitor</option>' + leitores
      .map(leitor => `<option value="${leitor.id}">${escaparHTML(leitor.nome)}</option>`).join('');
  } catch (error) { mostrarMensagem(error.message, 'error'); }
}

async function carregarEmprestimos() {
  listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Carregando...</td></tr>';
  try {
    const emprestimos = await apiFetch('api/emprestimos?status=abertos');
    if (!emprestimos.length) {
      listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Não há empréstimos em aberto.</td></tr>';
      return;
    }
    listaEmprestimos.innerHTML = emprestimos.map(item => `
      <tr><td>${item.id}</td><td>${escaparHTML(item.livro_titulo)}</td><td>${escaparHTML(item.leitor_nome)}</td>
      <td>${formatarData(item.data_emprestimo)}</td><td><button class="button button-small" data-devolver="${item.id}">Registrar devolução</button></td></tr>`).join('');
    listaEmprestimos.querySelectorAll('[data-devolver]').forEach(btn => btn.addEventListener('click', () => registrarDevolucao(btn.dataset.devolver)));
  } catch (error) {
    listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Não foi possível carregar os empréstimos.</td></tr>';
    mostrarMensagem(error.message, 'error');
  }
}

async function registrarDevolucao(id) {
  if (!confirm('Confirmar a devolução deste livro?')) return;
  try {
    const resultado = await apiFetch(`api/devolucoes/${id}`, { method: 'POST' });
    mostrarMensagem(resultado.mensagem);
    await Promise.all([carregarEmprestimos(), carregarOpcoes()]);
  } catch (error) { mostrarMensagem(error.message, 'error'); }
}

formEmprestimo.addEventListener('submit', async event => {
  event.preventDefault();
  const dados = { livro_id: Number(selectLivro.value), leitor_id: Number(selectLeitor.value) };
  try {
    const resultado = await apiFetch('api/emprestimos', { method: 'POST', body: dados });
    mostrarMensagem(resultado.mensagem);
    formEmprestimo.reset();
    await Promise.all([carregarEmprestimos(), carregarOpcoes()]);
  } catch (error) { mostrarMensagem(error.message, 'error'); }
});

document.getElementById('btnAtualizar').addEventListener('click', () => Promise.all([carregarEmprestimos(), carregarOpcoes()]));
carregarOpcoes();
carregarEmprestimos();
