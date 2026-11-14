<?php
declare(strict_types=1);

final class LivroService
{
    public function __construct(private LivroDAO $livroDAO) {}

    public function listar(): array
    {
        return $this->livroDAO->listar();
    }

    public function salvar(array $dados): array
    {
        $livro = Livro::fromArray($dados);
        $erro = $this->validar($livro);
        if ($erro !== null) {
            return ['erro' => $erro];
        }

        return ['id' => $this->livroDAO->salvar($livro), 'mensagem' => 'Livro cadastrado com sucesso.'];
    }

    public function editar(int $id, array $dados): array
    {
        if ($id < 1 || $this->livroDAO->buscarPorId($id) === null) {
            return ['erro' => 'Livro não encontrado.'];
        }

        $livro = Livro::fromArray($dados);
        $erro = $this->validar($livro);
        if ($erro !== null) {
            return ['erro' => $erro];
        }

        $this->livroDAO->editar($id, $livro);
        return ['mensagem' => 'Livro atualizado com sucesso.'];
    }

    public function excluir(int $id): array
    {
        if ($id < 1 || $this->livroDAO->buscarPorId($id) === null) {
            return ['erro' => 'Livro não encontrado.'];
        }
        if ($this->livroDAO->possuiEmprestimos($id)) {
            return ['erro' => 'Não é possível excluir um livro que possui empréstimos registrados.'];
        }

        $this->livroDAO->excluir($id);
        return ['mensagem' => 'Livro excluído com sucesso.'];
    }

    private function validar(Livro $livro): ?string
    {
        if ($livro->titulo === '' || $livro->autor === '') {
            return 'Título e autor são obrigatórios.';
        }
        $anoAtual = (int) date('Y');
        if ($livro->ano < 1450 || $livro->ano > $anoAtual) {
            return "O ano deve estar entre 1450 e {$anoAtual}.";
        }
        if ($livro->quantidade < 1) {
            return 'A quantidade de exemplares deve ser pelo menos 1.';
        }
        return null;
    }
}
