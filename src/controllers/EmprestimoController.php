<?php
declare(strict_types=1);

final class EmprestimoController
{
    public function __construct(private EmprestimoService $service) {}

    public function listar(bool $somenteAbertos = false): void
    {
        Response::json($this->service->listar($somenteAbertos));
    }

    public function emprestar(): void
    {
        $resultado = $this->service->emprestar(Request::body());
        Response::json($resultado, isset($resultado['erro']) ? 422 : 201);
    }

    public function devolver(int $id): void
    {
        $resultado = $this->service->devolver($id);
        Response::json($resultado, isset($resultado['erro']) ? 422 : 200);
    }
}
