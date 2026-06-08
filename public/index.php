<?php

require __DIR__ . '/../vendor/autoload.php';

use App\RouteProvider;
use App\ServiceProvider;
use Framework\Kernel;
use Framework\Request;

session_start();

if (isset($_GET['admin'])) {
    $_SESSION['is_admin'] = $_GET['admin'] === '1';
}

$config = array(
    'APP_ENV' => 'development',
    'VIEWS_PATH' => 'app/views',
    'APP_DB' => 'database.sqlite'
);

$kernel = new Kernel($config);

$kernel->registerServices(new ServiceProvider());

$kernel->registerRoutes(new RouteProvider());

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!is_string($urlPath)) {
    $urlPath = '/';
}

$queryParams = $_GET;

$postData = $_POST;

$request = new Request($method, $urlPath, $queryParams, $postData);

$response = $kernel->handle($request);

$response->echo();
