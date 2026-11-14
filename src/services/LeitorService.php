<?php
declare(strict_types=1);

final class LeitorService
{
    public function __construct(private LeitorDAO $leitorDAO) {}

    public function listar(): array
    {
        return $this->leitorDAO->listar();
    }

    public function salvar(array $dados): array
    {
        $leitor = Leitor::fromArray($dados);
        $erro = $this->validar($leitor);
        if ($erro !== null) {
            return ['erro' => $erro];
        }

        try {
            return ['id' => $this->leitorDAO->salvar($leitor), 'mensagem' => 'Leitor cadastrado com sucesso.'];
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                return ['erro' => 'Já existe um leitor cadastrado com esse e-mail.'];
            }
            throw $e;
        }
    }

    public function editar(int $id, array $dados): array
    {
        if ($id < 1 || $this->leitorDAO->buscarPorId($id) === null) {
            return ['erro' => 'Leitor não encontrado.'];
        }
        $leitor = Leitor::fromArray($dados);
        $erro = $this->validar($leitor);
        if ($erro !== null) {
            return ['erro' => $erro];
        }

        try {
            $this->leitorDAO->editar($id, $leitor);
            return ['mensagem' => 'Leitor atualizado com sucesso.'];
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                return ['erro' => 'Já existe um leitor cadastrado com esse e-mail.'];
            }
            throw $e;
        }
    }

    public function excluir(int $id): array
    {
        if ($id < 1 || $this->leitorDAO->buscarPorId($id) === null) {
            return ['erro' => 'Leitor não encontrado.'];
        }
        if ($this->leitorDAO->possuiEmprestimos($id)) {
            return ['erro' => 'Não é possível excluir um leitor que possui empréstimos registrados.'];
        }

        $this->leitorDAO->excluir($id);
        return ['mensagem' => 'Leitor excluído com sucesso.'];
    }

    private function validar(Leitor $leitor): ?string
    {
        if ($leitor->nome === '') {
            return 'O nome do leitor é obrigatório.';
        }
        if (!filter_var($leitor->email, FILTER_VALIDATE_EMAIL)) {
            return 'Informe um e-mail válido.';
        }
        return null;
    }
}
