<?php

use App\Controllers\DataMasterController;

$app = \Slim\Slim::getInstance();

$app->group('/datamaster', function () use ($app) {

    $app->get('/categorias', function () {
        (new DataMasterController())->getCategorias();
    });

    $app->get('/prioridades', function () {
        (new DataMasterController())->getPrioridades();
    });

    $app->get('/estados', function () {
        (new DataMasterController())->getEstados();
    });

});