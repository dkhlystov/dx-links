<?php

namespace dx\links\utils;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use dx\links\models\Domain;
use dx\links\models\Url;

class UrlParser
{

	/**
	 * Scan specified url
	 * @param Url $url 
	 * @return Url[]
	 */
	public static function scan(Url $url, $maxDepth = 5)
	{
		$domain = $url->domain;

		$content = Socket::request($domain->getDomain() . $url->url, $headers, $cookie, $t);

		//status
		$url->status = self::parseStatus($headers);

		//redirect
		$url->redirect = null;
		if (in_array($url->status, [301, 302]))
			$url->redirect = Socket::getLocation($headers);

		if ($url->status == 200){
			//last modified
			$url->lastModified = self::parseLastModified($headers);

			//expires
			$url->expires = self::parseExpires($headers);

			//title
			$url->title = self::parseTitle($content);

			//description
			$url->description = self::parseDescription($content);

			//keywords
			$url->keywords = self::parseKeywords($content);

			//size
			$url->size = strlen($content);

			//load time
			if ($t !== null)
				$url->loadTime = round($t * 1000);
		}

		$url->save(false);

		//urls
		$urls = [];
		if ($url->status == 200) {

			//prepare items
			$items = [];
			$domains = [];
			foreach (self::parseUrls($content) as $info) {
				$scheme = ArrayHelper::getValue($info, 'scheme', $domain->scheme);
				$host = ArrayHelper::getValue($info, 'host', $domain->host);
				$path = ArrayHelper::getValue($info, 'path', '/');
				$query = ArrayHelper::getValue($info, 'query');

				$d = $scheme . '://' . $host;
				$u = $path;
				if (mb_strpos($u, '/') !== 0)
					$u = parse_url($url->url, PHP_URL_PATH) . $u;
				if ($query)
					$u .= '?' . $query;

				$domains[$d] = ['scheme' => $scheme, 'host' => $host];

				if (mb_strlen($u) < 500)
					$items[] = ['d' => $d, 'u' => $u];
			}

			//prepare domains
			$tmp = [];
			foreach (Domain::find()->where(['host' => array_map(function($v) {return $v['host'];}, $domains)])->all() as $value)
				$tmp[$value->getDomain()] = $value;
			foreach ($domains as $key => $value) {
				if (array_key_exists($key, $tmp)) {
					$domains[$key] = $tmp[$key];
				} else {
					$d = new Domain($value);
					if ($d->save(false)) {
						$domains[$key] = $d;
					} else {
						unset($domains[$key]);
					}
				}
			}

			//create urls
			if ($url->depth < $maxDepth) {
				$depth = $url->depth + 1;
				foreach ($items as $item) {
					if (!array_key_exists($item['d'], $domains))
						continue;

					$domain = $domains[$item['d']];

					//determine url
					$u = Url::find()->where(['domain_id' => $domain->id, 'url' => $item['u']])->one();
					if ($u === null) {
						$u = new Url(['url' => $item['u'], 'depth' => $depth]);
						$domain->link('urls', $u);

						if ($u->domain_id == $url->domain_id)
							$urls[] = $u;
					} else {
						if ($url->domain_id == $u->domain_id && ($u->depth === null || $u->depth > $depth)) {
							$u->depth = $depth;
							$u->update(false, ['depth']);
						}
					}

					//link to origin
					$url->link('dest', $u);
				}
			}
		}

		return $urls;
	}

	/**
	 * Parse http status
	 * @param array $headers 
	 * @return integer|null
	 */
	private static function parseStatus($headers)
	{
		if (empty($headers))
			return null;

		if (!preg_match('/\d{3}/', $headers[0], $matches))
			return null;

		return (integer) $matches[0];
	}

	/**
	 * Parse last modified date
	 * @param array $headers 
	 * @return string|null
	 */
	private static function parseLastModified($headers)
	{
		$header = self::findHeader($headers, 'Last-Modified');

		$d = strtotime($header);
		if ($d === false)
			return null;

		return gmdate('Y-m-d H:i:s', $d);
	}

	/**
	 * Parse expires date
	 * @param array $headers 
	 * @return string|null
	 */
	private static function parseExpires($headers)
	{
		$header = self::findHeader($headers, 'Expires');

		$d = strtotime($header);
		if ($d === false)
			return null;

		return gmdate('Y-m-d H:i:s', $d);
	}

	/**
	 * Find specified header value
	 * @param array $headers 
	 * @param string $name 
	 * @return string
	 */
	private static function findHeader($headers, $name)
	{
		$name .= ':';
		$r = '';

		foreach ($headers as $header) {
			if (strpos($header, $name) === 0) {
				$r = $header;
				break;
			}
		}

		if ($r !== '')
			$r = trim(substr($r, strlen($name)));

		return $r;
	}

	/**
	 * Parse page title
	 * @param string $content 
	 * @return string|null
	 */
	private static function parseTitle($content)
	{
		if ($content === false)
			return null;

		if (!preg_match('/<title>(.*?)<\/title>/is', $content, $matches))
			return null;

		return trim(Html::decode($matches[1]));
	}

	/**
	 * Parse description
	 * @param string $content 
	 * @return string|null
	 */
	private static function parseDescription($content)
	{
		if ($content === false)
			return null;

		$c = preg_match('/<meta[^>]+name=(?:"description"|\'description\')[^>]+content=(?:"(.*?)"|\'(.*?)\')/is', $content, $matches);
		if ($c == 0)
			$c = preg_match('/<meta[^>]+content=(?:"(.*?)"|\'(.*?)\')[^>]+name=(?:"description"|\'description\')/is', $content, $matches);

		if ($c == 0)
			return null;

		return trim(Html::decode($matches[1]));
	}

	/**
	 * Parse keywords
	 * @param string $content 
	 * @return string|null
	 */
	private static function parseKeywords($content)
	{
		if ($content === false)
			return null;

		$c = preg_match('/<meta[^>]+name=(?:"keywords"|\'keywords\')[^>]+content=(?:"(.*?)"|\'(.*?)\')/is', $content, $matches);
		if ($c == 0)
			$c = preg_match('/<meta[^>]+content=(?:"(.*?)"|\'(.*?)\')[^>]+name=(?:"keywords"|\'keywords\')/is', $content, $matches);

		if ($c == 0)
			return null;

		return trim(Html::decode($matches[1]));
	}

	/**
	 * Parse urls from content
	 * @param string $content 
	 * @return array
	 */
	private static function parseUrls($content)
	{
		if ($content === false)
			return [];

		$urls = [];

		preg_match_all('/<a[^>]+href=(?:"(.*?)"|\'(.*?)\')/i', $content, $matches);
		foreach ($matches[1] as $value) {
			$value = str_replace('&amp;', '&', $value);

			//anchor
			if (mb_strpos($value, '#') === 0)
				continue;

			//protocols
			if (mb_strpos($value, 'tel:') === 0 || mb_strpos($value, 'mailto:') === 0 || mb_strpos($value, 'javascript:') === 0 || mb_strpos($value, 'file:') === 0 || mb_strpos($value, 'ftp:') === 0)
				continue;

			//files with extensions
			$ext = mb_strtolower(strrchr($value, '.'));
			if (in_array($ext, ['.jpg', '.jpeg', '.png', '.gif', '.doc', '.docx', '.xls', '.xlsx', '.pdf']))
				continue;

			$urls[] = parse_url($value);
		}

		return $urls;
	}

}

