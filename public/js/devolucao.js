const listaEmprestimos = document.getElementById('listaEmprestimos');

async function carregarEmprestimosAbertos() {
  listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Carregando...</td></tr>';
  try {
    const emprestimos = await apiFetch('api/emprestimos?status=abertos');
    if (!emprestimos.length) {
      listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Não há livros aguardando devolução.</td></tr>';
      return;
    }
    listaEmprestimos.innerHTML = emprestimos.map(item => `
      <tr><td>${item.id}</td><td>${escaparHTML(item.livro_titulo)}</td><td>${escaparHTML(item.leitor_nome)}</td>
      <td>${formatarData(item.data_emprestimo)}</td><td><button class="button button-small" data-devolver="${item.id}">Confirmar devolução</button></td></tr>`).join('');
    listaEmprestimos.querySelectorAll('[data-devolver]').forEach(btn => btn.addEventListener('click', () => registrarDevolucao(btn.dataset.devolver)));
  } catch (error) {
    listaEmprestimos.innerHTML = '<tr><td colspan="5" class="empty">Não foi possível carregar os empréstimos.</td></tr>';
    mostrarMensagem(error.message, 'error');
  }
}

async function registrarDevolucao(id) {
  if (!confirm('Deseja registrar a devolução deste exemplar?')) return;
  try {
    const resultado = await apiFetch(`api/devolucoes/${id}`, { method: 'POST' });
    mostrarMensagem(resultado.mensagem);
    carregarEmprestimosAbertos();
  } catch (error) { mostrarMensagem(error.message, 'error'); }
}

document.getElementById('btnAtualizar').addEventListener('click', carregarEmprestimosAbertos);
carregarEmprestimosAbertos();
