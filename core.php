<?php
error_reporting(E_ALL);
header('Content-type: text/html; charset=utf-8');

function scriptName() {
	if (!isset($_SERVER['SCRIPT_FILENAME']) or empty($_SERVER['SCRIPT_FILENAME'])) {
		return 'unknown';
	}
	return basename($_SERVER['SCRIPT_FILENAME'], ".php");
}

define('ROOT', __DIR__);
define('NAME', scriptName());
define('APP', ROOT.'/app');
define('LOCKS', ROOT.'/locks');
define('CLASSES', ROOT.'/classes');
define('LOGS', ROOT.'/logs');
define('APP_LOGS', LOGS.'/'.NAME);
define('COMPOSER', ROOT.'/vendor/autoload.php');

spl_autoload_register(function ($class) {
	if (!file_exists(CLASSES.'/'.$class.'.class.php') or is_dir(CLASSES.'/'.$class.'.class.php')) {
		Logger::send('|ОШИБКА| - Не удалось найти класс - "'.$class.'"');
		exit();
	}
	require_once(CLASSES.'/'.$class.'.class.php');
});

if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::send('|ОШИБКА| - Не установлены зависимости композера');
	exit();
}
require_once(COMPOSER);

$factory = new TH\Lock\FileFactory(Structure::get(LOCKS));
$lock = $factory->create(NAME);
try {
	$lock->acquire();
} catch (Exception $ex) {
	Logger::send('|БЛОК| - Скрипт "'.NAME.'" уже запущен');
	exit();
}