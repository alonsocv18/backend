<?php
require_once __DIR__ . '/../config/config.php';

$app = new \Slim\Slim();

$app->config('debug', true);

// Agrego el middleware de autenticación de manera global
// Entonces cada petición que llegue pasará primero por AuthMiddleware.
// El middleware decidirá si la ruta es pública o no.
$app->add(new \App\Middleware\AuthMiddleware());
$app->add(new \App\Middleware\CorsMiddleware());

// Carga de rutas
require_once __DIR__ . '/../rest/usuarios.php';
require_once __DIR__ . '/../rest/proyectos.php';
require_once __DIR__ . '/../rest/tareas.php';
require_once __DIR__ . '/../rest/datamaster.php';
require_once __DIR__ . '/../rest/reportes.php';

$app->run();