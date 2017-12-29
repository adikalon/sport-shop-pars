<?php

/**
 * Запросы на сервер
 */
class Request
{
	/**
	 * Запрос CURL'ом
	 *
	 * @param mixed $link Ссылка или опции
	 * @param integer $pause Задержка перед запросом в секундах
	 * @return string Ответ
	 */
	public static function curl($link = false, $pause = false)
	{
		if ($pause) {
			if (!is_numeric($pause) or $pause < 0) {
				Logger::send('|ОШИБКА| - Некорректное значение паузы CURL запроса: "'.$pause.'".');
				exit();
			}
			sleep((int)$pause);
		}
		$options = [];
		if (is_string($link)) {
			$options[CURLOPT_URL] = $link;
		} elseif (is_array($link)) {
			$options = $options + $link;
		} else {
			Logger::send('|ОШИБКА| - Переданы некорректные параметры в CURL.');
			exit();
		}
		if (!isset($options[CURLOPT_URL]) or empty($options[CURLOPT_URL])) {
			Logger::send('|ОШИБКА| - Не передан URL в CURL.');
			exit();
		}
		$options[CURLOPT_RETURNTRANSFER] = true;
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);
		curl_close($curl);
		unset($link, $pause, $options, $curl);
		return $result;
	}
}