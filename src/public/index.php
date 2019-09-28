<?php 

session_start();

// bootstrap application
require __DIR__ . '/../bootstrap/app.php';

// \Slim\App run
$app->run();