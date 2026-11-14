const formLivro = document.getElementById('formLivro');
const listaLivros = document.getElementById('listaLivros');
const anoInput = document.getElementById('ano');
anoInput.max = new Date().getFullYear();

async function carregarLivros() {
  listaLivros.innerHTML = '<tr><td colspan="7" class="empty">Carregando...</td></tr>';
  try {
    const livros = await apiFetch('api/livros');
    if (!livros.length) {
      listaLivros.innerHTML = '<tr><td colspan="7" class="empty">Nenhum livro cadastrado.</td></tr>';
      return;
    }
    listaLivros.innerHTML = livros.map(livro => `
      <tr>
        <td>${livro.id}</td><td>${escaparHTML(livro.titulo)}</td><td>${escaparHTML(livro.autor)}</td>
        <td>${livro.ano}</td><td>${livro.quantidade}</td><td>${livro.disponiveis}</td>
        <td class="actions"><button class="button button-small button-light" data-editar="${livro.id}">Editar</button><button class="button button-small button-danger" data-excluir="${livro.id}">Excluir</button></td>
      </tr>`).join('');
    listaLivros.querySelectorAll('[data-editar]').forEach(btn => btn.addEventListener('click', () => editarLivro(livros.find(l => l.id == btn.dataset.editar))));
    listaLivros.querySelectorAll('[data-excluir]').forEach(btn => btn.addEventListener('click', () => excluirLivro(btn.dataset.excluir)));
  } catch (error) {
    listaLivros.innerHTML = '<tr><td colspan="7" class="empty">Não foi possível carregar os livros.</td></tr>';
    mostrarMensagem(error.message, 'error');
  }
}

function editarLivro(livro) {
  document.getElementById('livroId').value = livro.id;
  document.getElementById('titulo').value = livro.titulo;
  document.getElementById('autor').value = livro.autor;
  document.getElementById('ano').value = livro.ano;
  document.getElementById('quantidade').value = livro.quantidade;
  document.getElementById('formTitulo').textContent = 'Editar livro';
  document.getElementById('btnSalvar').textContent = 'Salvar alterações';
  document.getElementById('btnCancelar').hidden = false;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function limparFormulario() {
  formLivro.reset();
  document.getElementById('livroId').value = '';
  document.getElementById('formTitulo').textContent = 'Cadastrar livro';
  document.getElementById('btnSalvar').textContent = 'Salvar livro';
  document.getElementById('btnCancelar').hidden = true;
}

async function excluirLivro(id) {
  if (!confirm('Deseja realmente excluir este livro?')) return;
  try {
    const resultado = await apiFetch(`api/livros/${id}`, { method: 'DELETE' });
    mostrarMensagem(resultado.mensagem);
    carregarLivros();
  } catch (error) { mostrarMensagem(error.message, 'error'); }
}

formLivro.addEventListener('submit', async event => {
  event.preventDefault();
  const id = document.getElementById('livroId').value;
  const dados = {
    titulo: document.getElementById('titulo').value,
    autor: document.getElementById('autor').value,
    ano: Number(document.getElementById('ano').value),
    quantidade: Number(document.getElementById('quantidade').value)
  };
  try {
    const resultado = await apiFetch(id ? `api/livros/${id}` : 'api/livros', {
      method: id ? 'PUT' : 'POST', body: dados
    });
    mostrarMensagem(resultado.mensagem);
    limparFormulario();
    carregarLivros();
  } catch (error) { mostrarMensagem(error.message, 'error'); }
});

document.getElementById('btnCancelar').addEventListener('click', limparFormulario);
document.getElementById('btnAtualizar').addEventListener('click', carregarLivros);
carregarLivros();
