<?php
declare(strict_types=1);

final class EmprestimoDAO
{
    public function __construct(private PDO $pdo) {}

    public function listar(bool $somenteAbertos = false): array
    {
        $sql = "SELECT e.id, e.livro_id, e.leitor_id, e.data_emprestimo, e.data_devolucao,
                       l.titulo AS livro_titulo, r.nome AS leitor_nome
                FROM emprestimos e
                INNER JOIN livros l ON l.id = e.livro_id
                INNER JOIN leitores r ON r.id = e.leitor_id";
        if ($somenteAbertos) {
            $sql .= ' WHERE e.data_devolucao IS NULL';
        }
        $sql .= ' ORDER BY e.data_emprestimo DESC, e.id DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function contarAbertosPorLivro(int $livroId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM emprestimos WHERE livro_id = ? AND data_devolucao IS NULL'
        );
        $stmt->execute([$livroId]);
        return (int) $stmt->fetchColumn();
    }

    public function contarAbertosPorLeitor(int $leitorId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM emprestimos WHERE leitor_id = ? AND data_devolucao IS NULL'
        );
        $stmt->execute([$leitorId]);
        return (int) $stmt->fetchColumn();
    }

    public function leitorPossuiLivroAberto(int $leitorId, int $livroId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM emprestimos
             WHERE leitor_id = ? AND livro_id = ? AND data_devolucao IS NULL'
        );
        $stmt->execute([$leitorId, $livroId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function salvar(Emprestimo $emprestimo): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO emprestimos (livro_id, leitor_id, data_emprestimo, data_devolucao)
             VALUES (?, ?, CURDATE(), NULL)'
        );
        $stmt->execute([$emprestimo->livro_id, $emprestimo->leitor_id]);
        return (int) $this->pdo->lastInsertId();
    }

    public function buscarPorIdParaAtualizar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM emprestimos WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function devolver(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE emprestimos SET data_devolucao = CURDATE()
             WHERE id = ? AND data_devolucao IS NULL'
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }
}
