<?php
declare(strict_types=1);

// O servidor embutido do PHP entrega arquivos estáticos diretamente.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require_once __DIR__ . '/../src/config/Database.php';
require_once __DIR__ . '/../src/core/Response.php';
require_once __DIR__ . '/../src/core/Request.php';
require_once __DIR__ . '/../src/models/Livro.php';
require_once __DIR__ . '/../src/models/Leitor.php';
require_once __DIR__ . '/../src/models/Emprestimo.php';
require_once __DIR__ . '/../src/dao/LivroDAO.php';
require_once __DIR__ . '/../src/dao/LeitorDAO.php';
require_once __DIR__ . '/../src/dao/EmprestimoDAO.php';
require_once __DIR__ . '/../src/services/LivroService.php';
require_once __DIR__ . '/../src/services/LeitorService.php';
require_once __DIR__ . '/../src/services/EmprestimoService.php';
require_once __DIR__ . '/../src/controllers/LivroController.php';
require_once __DIR__ . '/../src/controllers/LeitorController.php';
require_once __DIR__ . '/../src/controllers/EmprestimoController.php';
require_once __DIR__ . '/../src/proxy/ApiProxy.php';

if (str_starts_with($path, '/api/')) {
    (new ApiProxy())->handle();
    return;
}

if ($path === '/') {
    header('Location: index.html');
    exit;
}

http_response_code(404);
echo 'Página não encontrada.';
