<?php

require_once('/.composer/vendor/autoload.php');
require_once('/bitnami/magento/vendor/autoload.php');

require_once(__DIR__.'/vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
