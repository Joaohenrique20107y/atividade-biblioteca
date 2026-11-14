<?php
declare(strict_types=1);

final class ApiProxy
{
    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        // Também permite executar a pasta public dentro de um subdiretório do Apache/XAMPP.
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(dirname($scriptName), '/.');
        if ($basePath !== '' && str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
        }
        $path = rtrim($path, '/') ?: '/';

        try {
            $pdo = Database::getConnection();

            // Montagem das dependências: Controller -> Service -> DAO -> Model.
            $livroDAO = new LivroDAO($pdo);
            $leitorDAO = new LeitorDAO($pdo);
            $emprestimoDAO = new EmprestimoDAO($pdo);

            $livroController = new LivroController(new LivroService($livroDAO));
            $leitorController = new LeitorController(new LeitorService($leitorDAO));
            $emprestimoService = new EmprestimoService($pdo, $livroDAO, $leitorDAO, $emprestimoDAO);
            $emprestimoController = new EmprestimoController($emprestimoService);

            if (preg_match('#^/api/livros(?:/(\d+))?$#', $path, $matches)) {
                $id = isset($matches[1]) ? (int) $matches[1] : null;
                if ($method === 'GET' && $id === null) { $livroController->listar(); return; }
                if ($method === 'POST' && $id === null) { $livroController->salvar(); return; }
                if ($method === 'PUT' && $id !== null) { $livroController->editar($id); return; }
                if ($method === 'DELETE' && $id !== null) { $livroController->excluir($id); return; }
                Response::error('Método não permitido para livros.', 405); return;
            }

            if (preg_match('#^/api/leitores(?:/(\d+))?$#', $path, $matches)) {
                $id = isset($matches[1]) ? (int) $matches[1] : null;
                if ($method === 'GET' && $id === null) { $leitorController->listar(); return; }
                if ($method === 'POST' && $id === null) { $leitorController->salvar(); return; }
                if ($method === 'PUT' && $id !== null) { $leitorController->editar($id); return; }
                if ($method === 'DELETE' && $id !== null) { $leitorController->excluir($id); return; }
                Response::error('Método não permitido para leitores.', 405); return;
            }

            if ($path === '/api/emprestimos') {
                if ($method === 'GET') {
                    $abertos = ($_GET['status'] ?? '') === 'abertos';
                    $emprestimoController->listar($abertos); return;
                }
                if ($method === 'POST') { $emprestimoController->emprestar(); return; }
                Response::error('Método não permitido para empréstimos.', 405); return;
            }

            if (preg_match('#^/api/devolucoes/(\d+)$#', $path, $matches) && $method === 'POST') {
                $emprestimoController->devolver((int) $matches[1]); return;
            }

            Response::error('Rota da API não encontrada.', 404);
        } catch (Throwable $e) {
            error_log((string) $e);
            Response::error('Erro interno do servidor. Verifique a conexão com o banco de dados e os logs do PHP.', 500);
        }
    }
}
