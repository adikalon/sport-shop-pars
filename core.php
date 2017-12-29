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
define('PARSERS', ROOT.'/parsers');
define('LOGS', ROOT.'/logs');
define('APP_LOGS', LOGS.'/'.NAME);
define('COMPOSER', ROOT.'/vendor/autoload.php');


function includeClasses($class) {
	if (file_exists(CLASSES.'/'.$class.'.class.php') and !is_dir(CLASSES.'/'.$class.'.class.php')) {
		require_once(CLASSES.'/'.$class.'.class.php');
	}
}

function includeParsers($class) {
	if (!file_exists(PARSERS.'/'.$class.'.parser.php') or is_dir(PARSERS.'/'.$class.'.parser.php')) {
		Logger::send('|ОШИБКА| - Не удалось найти класс - "'.$class.'".');
		exit();
	}
	require_once(PARSERS.'/'.$class.'.parser.php');
}

spl_autoload_register('includeClasses');

$classes = [];

foreach(new DirectoryIterator(CLASSES) as $item) {
	if (!$item->isDot() and $item->isFile() and $item->getExtension() == 'php') {
		$classes[] = str_replace('.class', '', $item->getBasename('.php'));
	}
	unset($item);
}

foreach(new DirectoryIterator(PARSERS) as $item) {
	if (!$item->isDot() and $item->isFile() and $item->getExtension() == 'php') {
		$parser = str_replace('.parser', '', $item->getBasename('.php'));
		if (in_array($parser, $classes)) {
			Logger::send('|ОШИБКА| - Конфликт одинаковых имен классов - "'.$parser.'.parser.php" и "'.$parser.'.class.php".');
			exit();
		}
	}
	unset($item, $parser);
}

unset($classes);

spl_autoload_register('includeParsers');

if (!file_exists(COMPOSER) or is_dir(COMPOSER)) {
	Logger::send('|ОШИБКА| - Не установлены зависимости композера.');
	exit();
}
require_once(COMPOSER);

$factory = new TH\Lock\FileFactory(Structure::get(LOCKS));
$lock = $factory->create(NAME);
try {
	$lock->acquire();
} catch (Exception $ex) {
	Logger::send('|БЛОК| - Скрипт "'.NAME.'" уже запущен.');
	exit();
}