<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}


if ($_ENV['DROP_TABLE']) {
	passthru(sprintf(
		'php "%s/../bin/console" doctrine:schema:drop --env=%s --force --full-database',
		__DIR__,
		1
	));
}

if ($_ENV['DB_MIGRATION']) {
	passthru(sprintf(
		'php "%s/../bin/console" doctrine:migrations:migrate -n',
		__DIR__,
		1
	));
}