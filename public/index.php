<?php

use Pexess\Pexess;

require_once "../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable("../.");
$dotenv->load();

require_once "../src/app.php";

$app = Pexess::Application();

$app->init();