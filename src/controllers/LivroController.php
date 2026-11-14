<?php
declare(strict_types=1);

final class LivroController
{
    public function __construct(private LivroService $service) {}

    public function listar(): void
    {
        Response::json($this->service->listar());
    }

    public function salvar(): void
    {
        $resultado = $this->service->salvar(Request::body());
        Response::json($resultado, isset($resultado['erro']) ? 422 : 201);
    }

    public function editar(int $id): void
    {
        $resultado = $this->service->editar($id, Request::body());
        Response::json($resultado, isset($resultado['erro']) ? 422 : 200);
    }

    public function excluir(int $id): void
    {
        $resultado = $this->service->excluir($id);
        Response::json($resultado, isset($resultado['erro']) ? 422 : 200);
    }
}
