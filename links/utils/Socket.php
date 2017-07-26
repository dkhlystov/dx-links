<?php

namespace dx\links\utils;

class Socket {

	//parse http content
	protected static function http_content($response, &$headers = array(), &$cookie = array()) {
		//check for headers
		$i = strpos($response, "\r\n\r\n");
		if ($i === false) return '';
		//headers
		$h = substr($response, 0, $i);
		$headers = explode("\r\n", $h);
		//cookie
		foreach ($headers as $header) if (strpos(strtolower($header), 'set-cookie:') !== false) {
			$c = explode(';', substr($header, 11));
			foreach ($c as $value) {
				$a = explode('=', trim($value));
				if (sizeof($a) == 2 && $a[0] != 'Path' && $a[0] != 'Expires') $cookie[urldecode($a[0])] = urldecode($a[1]);
			}
		}
		//content
		$content = substr($response, $i + 4);
		if (strpos(strtolower($h), 'transfer-encoding: chunked') !== false)
			$content = self::unchunk($content);

		return $content;
	}
	private static function unchunk($data) {
	    $fp = 0;
	    $outData = "";
	    while ($fp < strlen($data)) {
	        $rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
	        $num = hexdec(trim($rawnum));
	        $fp += strlen($rawnum);
	        $chunk = substr($data, $fp, $num);
	        $outData .= $chunk;
	        $fp += strlen($chunk);
	    }
	    return $outData;
	}


	//==========================================================================
	// user functions
	//==========================================================================

	//request
	public static function request($options = array(), &$headers = array(), &$cookie = array(), &$time = null) {
		$userAgent1 = array(
			'iPad; CPU OS 5_1_1 like Mac OS X',
			'iPad; CPU OS 8_1 like Mac OS X',
			'Macintosh; Intel Mac OS X 10_9_5',
			'Macintosh; Intel Mac OS X 10.9; rv:32.0',
			'X11; Linux x86_64',
			'X11; Linux x86_64; rv:31.0',
			'X11; OpenBSD/5.5 armv7; rv:34.0',
			'X11; OpenBSD i386; rv:33.0',
			'X11; U; Linux i686; en-US; rv:1.2.1',
			'X11; Ubuntu; Linux i686; rv:32.0',
			'X11; Ubuntu; Linux x86_64; rv:33.0',
			'Windows NT 5.1',
			'Windows NT 5.1; rv:33.0',
			'Windows NT 6.1',
			'Windows NT 6.1; Trident/7.0; rv:11.0',
			'Windows NT 6.1; WOW64; rv:33.0',
			'Windows NT 6.2; WOW64',
		);
		$userAgent2 = array(
			'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.95 Safari/537.36',
			'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
			'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36',
			'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.13 Safari/537.36',
			'Gecko/20021204',
			'Gecko/20100101 Firefox/31.0',
			'Gecko/20100101 Firefox/33.0',
			'Gecko/20100101 Firefox/34.0',
			'like Gecko',
		);
		if (isset($options['proxy']) && !empty($options['proxy'])) {
			$a = explode(':', $options['proxy']);
			$options['server'] = $a[0];
			if (isset($a[1])) $options['port'] = $a[1];
		}
		if (is_string($options)) $options = array('url'=>$options);
		if (isset($options['url'])) {
			$info = parse_url($options['url']);
			if (!isset($info['host'])) return false;
			$options['host'] = $info['host'];
			$options['scheme'] = $info['scheme'];
			if (isset($info['port'])) $options['port'] = $info['port'];
			if (isset($info['path'])) $options['target'] = $info['path'];
			if (isset($info['query'])) $options['target'] .= '?'.$info['query'];
		}
		//check options
		if (!isset($options['scheme'])) $options['scheme'] = 'http';
		if (!isset($options['timeout'])) $options['timeout'] = 30;
		if (!isset($options['host'])) $options['host'] = 'ya.ru'; //dest host
		if (!isset($options['server'])) $options['server'] = ($options['scheme'] == 'https' ? 'ssl://' : '').$options['host']; //IP or host where query send
		if (!isset($options['port'])) $options['port'] = $options['scheme'] == 'https' ? 443 : 80; //port where query send
		if (!isset($options['target'])) $options['target'] = '/'; //dest script/file
		if (!isset($options['referer'])) $options['referer'] = 'http://ya.ru'; //referer
		if (!isset($options['get'])) $options['get'] = array(); //get params
		if (!isset($options['post'])) $options['post'] = array(); //post params
		if (!isset($options['cookie'])) $options['cookie'] = array(); //cookie params
		if (!isset($options['read'])) $options['read'] = true; //read from socket
		//method, data
		$method = 'GET';
		if (is_array($options['get']) && !empty($options['get'])) {
			$getValues = strpos($options['target'], '?') === false ? '?' : '&';
			foreach ($options['get'] as $name=>$value) $getValues .= urlencode($name).'='.urlencode($value).'&';
			$getValues = substr($getValues, 0, -1);
		} else $getValues = '';
		if (is_array($options['post']) && !empty($options['post'])) {
			$postValues = '';
			foreach ($options['post'] as $name=>$value) if (is_array($value)) {
				foreach ($value as $sub) $postValues .= urlencode($name).'='.urlencode($sub).'&';
			} else $postValues .= urlencode($name).'='.urlencode($value).'&';
			$postValues = substr($postValues, 0, -1);
			$method = 'POST';
		} else $postValues = '';
		if (is_array($options['cookie']) && !empty($options['cookie'])) {
			$cookieValues = '';
			foreach ($options['cookie'] as $name=>$value) $cookieValues .= urlencode($name).'='.urlencode($value).'; ';
			$cookieValues = substr($cookieValues, 0, -2);
		} else $cookieValues = '';
		//request making
		$request = "$method {$options['scheme']}://{$options['host']}{$options['target']}$getValues HTTP/1.1\r\n";
		$request .= "Host: {$options['host']}\r\n";
		$request .= "Referer: {$options['referer']}\r\n";
		$request .= "Accept-Language: en-us, en;q=0.50\r\n";
		$request .= "User-Agent: Mozilla/5.0 (".$userAgent1[array_rand($userAgent1)].") ".$userAgent2[array_rand($userAgent2)]."\r\n";
		if (!empty($cookieValues)) $request .= "Cookie: $cookieValues\r\n";
		if ($method == 'POST') {
			$length = strlen($postValues);
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: $length\r\n\r\n";
			$request .= $postValues;
		}
		$request .= "Connection: close\r\n\r\n";

		$fp = fsockopen($options['server'], $options['port']);
		if (!$fp) throw new Exception("Unable to open socket.");
		fwrite($fp, $request);
		if ($options['read'] == false) {
			stream_set_timeout($fp, 1);
			fread($fp, 256); //needs for transport init
		} else {
			$response = '';
			$start = microtime(true);
			while (!feof($fp)) $response .= fread($fp, 1024);
			$time = microtime(true) - $start;
			//debug
			if (defined('DEBUG') && DEBUG) @file_put_contents(dirname(__FILE__).'/../debug/socket_'.((int)round(microtime(true)*1000)), var_export($request, true).$response);
		}
		fclose($fp);

		return $options['read'] ? self::http_content($response, $headers, $cookie) : true;
	}

	//location determine
	public static function getLocation($headers) {
		$location = false;
		foreach ($headers as $header) if (strpos(strtolower($header), 'location:') !== false) {
			$location = trim(substr($header, 9));
			break;
		}
		return $location;
	}

	//content type determine
	public static function getContentType($headers) {
		$contentType = false;
		foreach ($headers as $header) if (strpos(strtolower($header), 'content-type:') !== false) {
			$contentType = trim(substr($header, 13));
			break;
		}
		return $contentType;
	}

}
