<?php

declare(strict_types=1);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});
// manual require for secrets.php (??)
require __DIR__ . "/src/secrets.php";

header("Content-type: application/json; charset=UTF-8");

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");


$parts = explode("/", $_SERVER["REQUEST_URI"]);
$id = $parts[3] ?? null;


$database = new Database("localhost", $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

$parcelGateway = new ParcelGateway($database);
$userGateway = new UserGateway($database);

$parcelController = new ParcelController($parcelGateway, $userGateway);
$userController = new UserController($userGateway);


if (!in_array($parts[2], ["users", "parcels"])) {
    http_response_code(404);
    exit;
} else if ($parts[2] == "users") {
    $userController->processRequest($_SERVER["REQUEST_METHOD"], $id);
} else if ($parts[2] == "parcels") {
    $parcelController->processRequest($_SERVER["REQUEST_METHOD"], $id);
}



/*
$database = new Database("localhost", $secrets['mysqlDb'], $secrets['mysqlUser'], $secrets['mysqlPass']);

$parcelGateway = new ParcelGateway($database);
$parcelController = new ParcelController($parcelGateway);

$userGateway = new UserGateway($database);
$userController = new UserController($gateway);

$parcelController->processRequest($_SERVER["REQUEST_METHOD"], $id);*/