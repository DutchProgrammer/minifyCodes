<?php

class minify {
	private $ch = false;

	function __construct() {
		$this->ch = curl_init();
	}
	function __destruct() {
		curl_close($this->ch);
	}

	function mergeFiles($files=[], $saveToFile=false) {

		$code = '';

		foreach ($files as $file) {
			if (!file_exists($file)) {
				echo $file.' does not exists'.PHP_EOL;
				exit;
			}

			$pathSplit          = explode('.', $file);
			$extention          = $pathSplit[count($pathSplit)-1];
			
			$filename           = basename($file);
			$filePath           = str_replace($filename, '', $file);
			
			$filePathSplit      = explode('/', $filePath);
			$filePathSplitTotal = count($filePathSplit);

			if (empty($filePathSplit[$filePathSplitTotal-1])) {
				unset($filePathSplit[$filePathSplitTotal-1]);
				--$filePathSplitTotal;
			}

			unset($filePathSplit[0]);
			--$filePathSplitTotal;
			$filePathSplit = array_merge([], $filePathSplit);

			$content = file_get_contents($file);

			//Strip off comments
			$regex = [
				"`^([\t\s]+)`ism"                       => '',
				"`^\/\*(.+?)\*\/`ism"                   => "",
				"`([\n\A;]+)\/\*(.+?)\*\/`ism"          => "$1",
				"`([\n\A;\s]+)//(.+?)[\n\r]`ism"        => "$1\n",
				"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n"
			];
			
			$content = preg_replace(array_keys($regex),$regex,$content);

			if ($extention === 'css') {

				//Remove last folder
				unset($filePathSplit[$filePathSplitTotal-1]);
				--$filePathSplitTotal;

				if ($filePathSplitTotal === 0 || ($filePathSplitTotal === 1 && empty($filePathSplit[0]))) {
					$newPath = '';
				} else {
					$newPath = '/'.join('/', $filePathSplit);
				}

				$content = str_replace("url('../", "url('".$newPath."/", $content);
				$content = str_replace("url('./", "url('".$filePath, $content);

			} elseif ($extention === 'js') {


			}

			$code .= $content;
			unset($content);
		}

		if ($saveToFile) {
			if (file_put_contents($saveToFile, $code)) {
				return true;
			}

			return false;
		} else {
			return $code;
		}
	}

	function compressCss($file=false, $saveToFile=false) {

		if (!$file || !file_exists($file)) {
			echo 'no compressCss';
			return false;
		}

		$postData = [
			'code' => file_get_contents($file),
			'options' => [
				'disable-optimizations' => 'false',
				'line-break'            => '',
				'nomunge'               => 'false',
				'preserve-semi'         => 'false',
				'verbose'               => 'false',
			],
			'type' => 'css'
		];

		curl_setopt($this->ch, CURLOPT_URL, 'https://refresh-sf.herokuapp.com/yui/');
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_USERAGENT, "Danny van der Knaap minifier");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));

		$minifiedCode = curl_exec($this->ch);
		$minifiedCode = json_decode($minifiedCode, 1);

		if (!isset($minifiedCode['code']) || empty($minifiedCode['code'])) {
			echo 'couldnt compress file';
			exit;
		} 

		$minifiedCode = $minifiedCode['code'];

		if ($saveToFile) {

			if (!is_string($saveToFile)) {
				$saveToFile = $file;
			}
			if (file_put_contents($saveToFile, $minifiedCode)) {
				return true;
			}

			return false;
		} else {
			return $minifiedCode;
		}
	}

	function compressJs($file=false, $saveToFile=false) {

		if (!$file || !file_exists($file)) {
			echo 'no compressJs';
			return false;
		}

		$postData = [
			'code' => file_get_contents($file),
			'options' => [
				'disable-optimizations' => 'false',
				'line-break'            => '',
				'nomunge'               => 'false',
				'preserve-semi'         => 'false',
				'verbose'               => 'false',
			],
			'type' => 'javascript'
		];

		curl_setopt($this->ch, CURLOPT_URL, 'https://refresh-sf.herokuapp.com/yui/');
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_USERAGENT, "Danny van der Knaap minifier");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));

		$minifiedCode = curl_exec($this->ch);
		$minifiedCode = json_decode($minifiedCode, 1);

		if (!isset($minifiedCode['code']) || empty($minifiedCode['code'])) {
			echo 'couldnt compress file';
			exit;
		}

		$minifiedCode = $minifiedCode['code'];

		if ($saveToFile) {

			if (!is_string($saveToFile)) {
				$saveToFile = $file;
			}
			if (file_put_contents($saveToFile, $minifiedCode)) {
				return true;
			}

			return false;
		} else {
			return $minifiedCode;
		}
	}

	function compressHtml($file=false, $saveToFile=false) {
		if (!$file || !file_exists($file)) {
			return false;
		}

		//$postData = 'code='.$this->encodeURIComponent(file_get_contents($file)).'&code_type=smarty&html_force_trim=1&html_level=2&html_single_line=1&js_engine=yui&js_fallback=1&js_ph_engine=yui&js_ph_fallback=1&minimize_css=1&minimize_events=1&minimize_js=1&minimize_js_href=1&minimize_js_ph=1&minimize_style=1&smarty_auto_literals=1&smarty_mode=1&verbose=1';

		$postData = [
			'code'                 => file_get_contents($file),
			'code_type'            => 'smarty',
			'html_force_trim'      => 1,
			'html_level'           => 2,
			'html_single_line'     => 1,
			'js_engine'            => 'yui',
			'js_fallback'          => 1,
			'js_ph_engine'         => 'yui',
			'js_ph_fallback'       => 1,
			'minimize_css'         => 1,
			'minimize_events'      => 1,
			'minimize_js'          => 1,
			'minimize_js_href'     => 1,
			'minimize_js_ph'       => 1,
			'minimize_style'       => 1,
			'smarty_auto_literals' => 1,
			'smarty_mode'          => 1,
			'verbose'              => 1
		];

		curl_setopt($this->ch, CURLOPT_URL, 'https://htmlcompressor.com/compress_ajax_v2.php');
		curl_setopt($this->ch, CURLOPT_HEADER, 0);
		curl_setopt($this->ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($this->ch, CURLOPT_REFERER, 'https://htmlcompressor.com/compressor/');
		curl_setopt($this->ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0 Danny van der Knaap minifier");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($postData));
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["Expect:  "]);

		$response     = curl_exec($this->ch);
		$minifiedCode = json_decode($response, 1);

		if ( ($error = json_last_error()) || !$minifiedCode['success']) {


			echo $file.' couldnt compress file'.PHP_EOL;
			print_r($postData);
			print_r($response);
			var_dump($minifiedCode);
			print_r($error);

			$info = curl_getinfo($this->ch);
			print_r($info);
			return false;
		} 

		$minifiedCode = $minifiedCode['result'];

		if ($saveToFile) {

			if (!is_string($saveToFile)) {
				$saveToFile = $file;
			}
			if (file_put_contents($saveToFile, $minifiedCode)) {
				return true;
			}

			return false;
		} else {
			return $minifiedCode;
		}
	}

	private function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
	}
}
