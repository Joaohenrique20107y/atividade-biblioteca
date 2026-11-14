<?php
declare(strict_types=1);

final class LeitorDAO
{
    public function __construct(private PDO $pdo) {}

    public function listar(): array
    {
        return $this->pdo->query('SELECT id, nome, email FROM leitores ORDER BY nome')->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, email FROM leitores WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorIdParaAtualizar(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, nome, email FROM leitores WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function salvar(Leitor $leitor): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO leitores (nome, email) VALUES (?, ?)');
        $stmt->execute([$leitor->nome, $leitor->email]);
        return (int) $this->pdo->lastInsertId();
    }

    public function editar(int $id, Leitor $leitor): bool
    {
        $stmt = $this->pdo->prepare('UPDATE leitores SET nome = ?, email = ? WHERE id = ?');
        $stmt->execute([$leitor->nome, $leitor->email, $id]);
        return $stmt->rowCount() > 0 || $this->buscarPorId($id) !== null;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM leitores WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function possuiEmprestimos(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM emprestimos WHERE leitor_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
