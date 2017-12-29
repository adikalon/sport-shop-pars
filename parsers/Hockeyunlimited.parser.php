<?php

/**
 * Парсер для сайта https://www.hockeyunlimited.fi/
 */
class Hockeyunlimited
{
	/**
	 * @var string Ссылка на категорию
	 */
	public $category = '';
	
	/**
	 * @var string Название категории
	 */
	public $name = '';
	
	/**
	 * @var array Набор параметров категории
	 */
	public $params = [];
	
	/**
	 * @var integer Дальше какой страницы не ходить. 0 - обходить все
	 */
	public $pages = 0;
	
	/**
	 * @var integer Количество товаров на одной странице
	 */
	public $size = 500;
	
	/**
	 * @var array Опции для CURL'а
	 */
	public $options = [];
	
	/**
	 * @var integer Пауза перед CURL запросом (в секундах)
	 */
	public $pause = 0;
	
	/**
	 * @var string Путь к CSV-файлу
	 */
	public $csv = '';
	
	/**
	 * @var string Путь к файлу с последним id для csv
	 */
	public $increment = '';
	
	/**
	 * @var string Путь к месту сохранения картинок
	 */
	public $images = '';
	
	public function __construct()
	{
		Logger::send('|СТАРТ| - Запущен скрипт "'.NAME.'".');
	}
	
	public function __destruct()
	{
		Logger::send('|КОНЕЦ| - Работа скрипта "'.NAME.'" завершена.');
	}
	
	/**
	 * Запуск парсера
	 */
	public function start()
	{
		$this->validation();
		for ($page = 1; $page <= $this->pages; $page++) {
			Logger::send('|ПАРСИНГ| - Категория: "'.$this->name.'". Страница: '.$page.' из '.$this->pages.'.');
			$this->parsPage($page);
		}
		Logger::send('|ПАРСИНГ| - Обход категории "'.$this->name.'" окончен.');
	}
	
	/**
	 * Получение линков объявлений со страницы
	 * 
	 * @param integer $page Номер страницы
	 */
	private function parsPage($page)
	{
		$options = $this->options;
		$options[CURLOPT_URL] = $this->category.'&PageSize='.$this->size.'&Page='.$page;
		$html = Request::curl($options, $this->pause);
		$dom = phpQuery::newDocument($html);
		$goods = $dom->find('h3.TopPaddingWide>a');
		$hrefs = [];
		foreach ($goods as $good) {
			$hrefs[] = 'https://www.hockeyunlimited.fi/epages/hockeyunlimited.sf/fi_FI/'.pq($good)->attr('href');
			unset($good);
		}
		$dom->unloadDocument();
		unset($page, $options, $html, $dom, $goods);
		if (empty($hrefs)) {
			Logger::send("|ОШИБКА| - Не удалось получить список товаров");
			exit();
		}
		foreach ($hrefs as $href) {
			$this->parseGood($href);
			unset($href);
		}
	}

	/**
	 * Установка кол-ва страниц категории
	 * 
	 * @return integer Кол-во страниц категории
	 */
	private function pagesCount()
	{
		$options = $this->options;
		$options[CURLOPT_URL] = $this->category.'&PageSize='.$this->size.'&Page=1';
		$html = Request::curl($options, $this->pause);
		$dom = phpQuery::newDocument($html);
		$marker = $dom->find('a[rel=next]');
		if (empty($html)) {
			Logger::send('|ОШИБКА| - Не удалось получить количество страниц категории.');
			exit();
		}
		if (count($marker) < 1) {
			$dom->unloadDocument();
			unset($options, $html, $dom, $marker);
			return 1;
		}
		$pages[] = 1;
		foreach ($marker as $mark) {
			$cur = pq($mark)->text();
			if (is_numeric($cur)) {
				$pages[] = $cur;
			}
			unset($mark);
		}
		$dom->unloadDocument();
		unset($options, $html, $dom, $marker, $cur);
		return max($pages);
	}

	/**
	 * Валидация всех установленных параметров
	 */
	private function validation()
	{
		if (empty($this->category) or !is_string($this->category)) {
			Logger::send('|ОШИБКА| - Не установлена ссылка на категорию.');
			exit();
		}
		if (empty($this->name) or !is_string($this->name)) {
			Logger::send('|ОШИБКА| - Не установлено название категории.');
			exit();
		}
		if (!is_array($this->params)) {
			Logger::send('|ОШИБКА| - Некорректный формат параметров товара.');
			exit();
		}
		if (!is_numeric($this->pages)) {
			Logger::send('|ОШИБКА| - Некорректное значение для ограничителя страницы.');
			exit();
		}
		if ($this->pages < 1) {
			$this->pages = $this->pagesCount();
		}
		if (!is_numeric($this->size) or $this->size < 1) {
			Logger::send('|ОШИБКА| - Некорректное значение для кол-ва товаров на странице.');
			exit();
		}
		if (!is_array($this->options)) {
			Logger::send('|ОШИБКА| - Некорректные параметры для CURL.');
			exit();
		}
		if (!is_numeric($this->pause) or $this->pause < 0) {
			Logger::send('|ОШИБКА| - Некорректное значение паузы.');
			exit();
		}
		if (empty($this->csv) or !is_string($this->csv)) {
			Logger::send('|ОШИБКА| - Некорректный путь для сохранения CSV.');
			exit();
		}
		if (empty($this->increment) or !is_string($this->increment)) {
			Logger::send('|ОШИБКА| - Некорректный путь сохранения ID для CSV.');
			exit();
		}
		if (empty($this->images) or !is_string($this->images)) {
			Logger::send('|ОШИБКА| - Некорректный путь для сохранения фотографий.');
			exit();
		}
	}
}
