<?php

/**
 * Поддержка корректной структуры приложения
 */
class Structure
{
	/**
	 * Проверка существования пути
	 * 
	 * @param string $path Путь
	 * @param boolean $file Если передать true - последний элемент строки будет файлом
	 * @return string Полный путь
	 */
	public static function get($path, $file = false)
	{
		if ($file and (!file_exists($path) or is_dir($path))) {
			return self::correction($path, $file);
		}
		if (!$file and (!file_exists($path) or !is_dir($path))) {
			return self::correction($path, $file);
		}
		return $path;
	}
	
	/**
	 * Создание нужных дирректорий и файлов
	 * 
	 * @param string $path Путь
	 * @param boolean $file Если передать true - последний элемент строки будет файлом
	 * @return string Полный путь
	 */
	private static function correction($path, $file = false)
	{
		$path = str_replace(ROOT, '', $path);
		$path = str_replace('\\', '/', $path);
		$path = preg_replace(['/^\/+/', '/\/+$/'], '', $path);
		$structure = explode('/', $path);
		$count = count($structure);
		if ($file) {
			$fileName = $structure[$count-1];
		}
		$dir = ROOT;
		foreach ($structure as $elem) {
			$dir .= '/'.$elem;
			if (!self::validate($elem)) {
				Logger::send('|ОШИБКА| - Недопустимые символы в имени файла или папки: "'.$dir.'"');
				exit();
			}
			if ($file and $elem == $fileName) {
				if (!file_exists($dir) or is_dir($dir)) {
					$fp = fopen($dir, 'ab');
					if (!$fp) {
						Logger::send('|ОШИБКА| - Не удалось создать файл: "'.$dir.'"');
						exit();
					}
					fclose($fp);
					unset($fp);
				}
			} else {
				if (!file_exists($dir) or !is_dir($dir)) {
					if (!mkdir($dir)) {
						Logger::send('|ОШИБКА| - Не удалось создать папку: "'.$dir.'"');
						exit();
					}
				}
			}
			unset($elem);
		}
		unset($path, $file, $structure, $count, $fileName);
		return $dir;
	}
	
	/**
	 * Валидация имени файла / папки
	 * 
	 * @param string $name Имя
	 * @return boolean Корректное / некорректное
	 */
	private static function validate($name)
	{
		return !preg_match('/[#%&\*\|:"\<\>\?]+/', $name);
	}
}
