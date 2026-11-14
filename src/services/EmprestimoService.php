<?php
declare(strict_types=1);

final class EmprestimoService
{
    public function __construct(
        private PDO $pdo,
        private LivroDAO $livroDAO,
        private LeitorDAO $leitorDAO,
        private EmprestimoDAO $emprestimoDAO
    ) {}

    public function listar(bool $somenteAbertos = false): array
    {
        return $this->emprestimoDAO->listar($somenteAbertos);
    }

    public function emprestar(array $dados): array
    {
        $livroId = (int) ($dados['livro_id'] ?? 0);
        $leitorId = (int) ($dados['leitor_id'] ?? 0);
        if ($livroId < 1 || $leitorId < 1) {
            return ['erro' => 'Selecione um livro e um leitor.'];
        }

        $this->pdo->beginTransaction();
        try {
            // O bloqueio da linha do livro evita que duas requisições usem o mesmo último exemplar.
            $livro = $this->livroDAO->buscarPorIdParaAtualizar($livroId);
            if ($livro === null) {
                $this->pdo->rollBack();
                return ['erro' => 'Livro não encontrado.'];
            }
            if ($this->leitorDAO->buscarPorIdParaAtualizar($leitorId) === null) {
                $this->pdo->rollBack();
                return ['erro' => 'Leitor não encontrado.'];
            }

            // R3 — quantidade menos empréstimos em aberto precisa ser maior que zero.
            $abertosDoLivro = $this->emprestimoDAO->contarAbertosPorLivro($livroId);
            if (((int) $livro['quantidade'] - $abertosDoLivro) < 1) {
                $this->pdo->rollBack();
                return ['erro' => 'Não há exemplares disponíveis desse livro.'];
            }

            // R4 — cada leitor pode ter no máximo três empréstimos em aberto.
            if ($this->emprestimoDAO->contarAbertosPorLeitor($leitorId) >= 3) {
                $this->pdo->rollBack();
                return ['erro' => 'O leitor já possui o limite de 3 empréstimos em aberto.'];
            }

            // R5 — o leitor não pode retirar outro exemplar do mesmo livro enquanto não devolver.
            if ($this->emprestimoDAO->leitorPossuiLivroAberto($leitorId, $livroId)) {
                $this->pdo->rollBack();
                return ['erro' => 'Esse leitor já possui um exemplar desse livro emprestado.'];
            }

            $id = $this->emprestimoDAO->salvar(new Emprestimo(null, $livroId, $leitorId));
            $this->pdo->commit();
            return ['id' => $id, 'mensagem' => 'Empréstimo registrado com sucesso.'];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function devolver(int $id): array
    {
        if ($id < 1) {
            return ['erro' => 'Empréstimo inválido.'];
        }

        $this->pdo->beginTransaction();
        try {
            $emprestimo = $this->emprestimoDAO->buscarPorIdParaAtualizar($id);
            if ($emprestimo === null) {
                $this->pdo->rollBack();
                return ['erro' => 'Empréstimo não encontrado.'];
            }

            // R6 — somente empréstimos em aberto podem ser devolvidos.
            if ($emprestimo['data_devolucao'] !== null) {
                $this->pdo->rollBack();
                return ['erro' => 'Esse empréstimo já foi devolvido.'];
            }

            $this->emprestimoDAO->devolver($id); // grava a data de hoje com CURDATE().
            $this->pdo->commit();
            return ['mensagem' => 'Devolução registrada com sucesso.'];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
