<?php

// Autoload dependencies and classes
require __DIR__ . '/../vendor/autoload.php';

use App\RouteProvider;
use App\ServiceProvider;
use Framework\Kernel;
use Framework\Request;

// Start session so we can track admin state
session_start();

// Dev toggle: visit /?admin=1 to become admin, /?admin=0 to stop
if (isset($_GET['admin'])) {
    $_SESSION['is_admin'] = $_GET['admin'] === '1';
}

$config = array(
    'APP_ENV' => 'development',
    'VIEWS_PATH' => 'app/views',
    'APP_DB' => 'database.sqlite'
);

// Initialize the Kernel with configuration
$kernel = new Kernel($config);

$kernel->registerServices(new ServiceProvider());

// Define routes
$kernel->registerRoutes(new RouteProvider());

// Get Request data from the global variables
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Extract the path from the URL
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!is_string($urlPath)) {
    $urlPath = '/';
}

// Get query (GET) parameters
$queryParams = $_GET;

// Get POST data
$postData = $_POST;

// Create the Request object
$request = new Request($method, $urlPath, $queryParams, $postData);

// Handle the request and get the response
$response = $kernel->handle($request);

// Send the response to the client
$response->echo();
