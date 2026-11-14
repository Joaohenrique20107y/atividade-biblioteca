<?php
declare(strict_types=1);

final class LivroDAO
{
    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        $sql = "SELECT l.*,
                    l.quantidade - (
                        SELECT COUNT(*) FROM emprestimos e
                        WHERE e.livro_id = l.id AND e.data_devolucao IS NULL
                    ) AS disponiveis
                FROM livros l ORDER BY l.titulo";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM livros WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorIdParaAtualizar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM livros WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function salvar(Livro $livro): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO livros (titulo, autor, ano, quantidade) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$livro->titulo, $livro->autor, $livro->ano, $livro->quantidade]);
        return (int) $this->pdo->lastInsertId();
    }

    public function editar(int $id, Livro $livro): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE livros SET titulo = ?, autor = ?, ano = ?, quantidade = ? WHERE id = ?'
        );
        $stmt->execute([$livro->titulo, $livro->autor, $livro->ano, $livro->quantidade, $id]);
        return $stmt->rowCount() > 0 || $this->buscarPorId($id) !== null;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM livros WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function possuiEmprestimos(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM emprestimos WHERE livro_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
