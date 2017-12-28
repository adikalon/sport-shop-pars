<?php

/**
 * Логирование
 */
class Logger
{
	/**
	 * Вывод лога в консоль
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function console($message)
	{
		echo "[".date('H:i:s')."]: ".mb_convert_encoding($message, 'UTF-8', 'auto')."\n";
		unset($message);
	}
	
	/**
	 * Запись лога в файл
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function file($message)
	{
		file_put_contents(
			Structure::get(APP_LOGS)."/".date('Y-m-d').".txt",
			"[".date('H:i:s')."]: ".mb_convert_encoding($message, 'UTF-8', 'auto')."\r\n",
			FILE_APPEND
		);
		unset($message);
	}
	
	/**
	 * Запись лога в файл и вывод в консоль
	 * 
	 * @param string $message Текст сообщения
	 */
	public static function send($message)
	{
		echo "[".date('H:i:s')."]: ".$message."\n";
		file_put_contents(
			Structure::get(APP_LOGS)."/".date('Y-m-d').".txt",
			"[".date('H:i:s')."]: ".mb_convert_encoding($message, 'UTF-8', 'auto')."\r\n",
			FILE_APPEND
		);
		unset($message);
	}
}