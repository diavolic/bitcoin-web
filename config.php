<?php


	class bitcoin {
	    private $username;
	    private $password;
	    private $proto;
	    private $host;
	    private $port;
	    private $url;
	    private $CACertificate;
	
	    public $status;
	    public $error;
	    public $raw_response;
	    public $response;

		public $additional;
	
	    private $id = 0;
	
	    public function __construct($username, $password, $host = 'localhost', $port = 8332, $url = null) {
	        $this->username      = $username;
	        $this->password      = $password;
	        $this->host          = $host;
	        $this->port          = $port;
	        $this->url           = $url;
	
	        // Set some defaults
	        $this->proto         = 'http';
	        $this->CACertificate = null;
			$this->additional =  nl2br("\nUseful links:\n
									<a target='_blank' href='https://www.reddit.com/r/Bitcoin/comments/4yiwsy/bitcoinqt_ready_for_use_within_half_an_hour/'>bitcoin-qt ready for use within half an hour … download an up-to-date pruned blockchain</a>\n
									<a target='_blank' href='https://drive.google.com/drive/folders/0B0nH34wIYOSlSG81ZUZUZGZjVkE'>Last pruned blocks</a>\n
									<a target='_blank' href='https://bitcoin.org/en/download'>Download Bitcoin Core</a>\n
									<a target='_blank' href='bitcoin.conf.txt'>Example bitcoind configuration with proone option</a>");
	    }
	
	    public function setSSL($certificate = null) {
	        $this->proto         = 'https'; // force HTTPS
	        $this->CACertificate = $certificate;
	    }
	
	    public function __call($method, $params) {
	        $this->status       = null;
	        $this->error        = null;
	        $this->raw_response = null;
	        $this->response     = null;
	        $params = array_values($params);
	        $this->id++;
	        $request = json_encode(array(
	            'method' => $method,
	            'params' => $params,
	            'id'     => $this->id
	        ));
	        $curl    = curl_init("{$this->proto}://{$this->host}:{$this->port}/{$this->url}");
	        $options = array(
	            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
	            CURLOPT_USERPWD        => $this->username . ':' . $this->password,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_FOLLOWLOCATION => true,
	            CURLOPT_MAXREDIRS      => 10,
	            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
	            CURLOPT_POST           => true,
	            CURLOPT_POSTFIELDS     => $request
	        );
	        if (ini_get('open_basedir')) {
	            unset($options[CURLOPT_FOLLOWLOCATION]);
	        }
	
	        if ($this->proto == 'https') {
	            if (!empty($this->CACertificate)) {
	                $options[CURLOPT_CAINFO] = $this->CACertificate;
	                $options[CURLOPT_CAPATH] = DIRNAME($this->CACertificate);
	            } else {
	                $options[CURLOPT_SSL_VERIFYPEER] = false;
	            }
	        }
	        curl_setopt_array($curl, $options);
	        $this->raw_response = curl_exec($curl);
	        $this->response     = json_decode($this->raw_response, true);
	        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	        $curl_error = curl_error($curl);
	        curl_close($curl);
	        if (!empty($curl_error)) {
	            $this->error = $curl_error;
	        }
	        if ($this->response['error']) {
	            $this->error = $this->response['error']['message'];
	        } elseif ($this->status != 200) {
	            switch ($this->status) {
	                case 400:
	                    $this->error = 'HTTP_BAD_REQUEST';
	                    break;
	                case 401:
	                    $this->error = 'HTTP_UNAUTHORIZED';
	                    break;
	                case 403:
	                    $this->error = 'HTTP_FORBIDDEN';
	                    break;
	                case 404:
	                    $this->error = 'HTTP_NOT_FOUND';
	                    break;
	            }
	        }
	
	        if ($this->error) {
	            return false;
	        }
	
	        return $this->response['result'];
	    }

		public function getPath() {
			if ($j = $this->getrpcinfo()) {
				return dirname($j["logpath"]);
			} else {
				return -1;
			}
		}

		public function check(){
			if (!$this->getbalances()) {
				die ($this->error.PHP_EOL.$this->additional);
			} else {
				return true;
			}
		}
	}

	class variables {
		var $g; // $_GET
		var $p; // $_POST
		var $f; // $_FILES
		var $s; // $_SESSION
		var $srv;  // $_SERVER
		var $db; // mysql database class
		var $c; // config array
		var $act; // act variable
		var $subact; // subact variable
		var $type = "btc";
		public $book_request = array();
		public $book_send = array();

		function __construct ($file="") { // $db - current  db object
			@session_start();
			$this->g = $_GET;
			$this->p = $_POST;
			$this->f = $_FILES;
			$this->s = $_SESSION;
			$this->srv = $_SERVER;
			$this->file = $file ? $file : dirname(__FILE__)."/config.ini";
			$this->c = $this->read();
			if ($this->c["internal_book_request"]) {
				$this->book_request = json_decode(base64_decode($this->c["internal_book_request"]), true);
			}
			if ($this->c["internal_book_send"]) {
				$this->book_send = json_decode(base64_decode($this->c["internal_book_send"]), true);
			}
			$this->act = "";
			$this->act = $this->clean($this->g["act"]);
		}

		function write (){
	        $data = array();
			$data[] = "[Panel Variables]";
			$this->c["internal_book_request"] = base64_encode(json_encode($this->book_request));
			$this->c["internal_book_send"] = base64_encode(json_encode($this->book_send));
			foreach ( $this->c as $skey => $sval) {
				$data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
			}
	        $fp = fopen($this->file, 'w');
	        $retries = 0;
	        $max_retries = 100;
	        if (!$fp) {
	            return false;
	        }
	        do {
	            if ($retries > 0) {
	                usleep(rand(1, 5000));
	            }
	            $retries += 1;
	        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);
	        if ($retries == $max_retries) {
	            return false;
	        }
	        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);
	        flock($fp, LOCK_UN);
	        fclose($fp);
	        return true;
		}

		function read() {
			$this->c = parse_ini_file($this->file);
			return parse_ini_file($this->file);
		}

		function sset($name, $val = "") {
			$_SESSION[$name] = $val;
			$this->s = $_SESSION;
		}

		function cleang ($str, $mode = 0) {
			return isset ($this->g[$str]) ? $this->clean ($this->g[$str]) : false;
		}

		function cleanp ($str, $mode = 0) {
			return isset($this->p[$str]) ? $this->clean ($this->p[$str]) : false;
		}
		
		function cleans ($str, $mode = 0) {
			return isset ($this->s[$str]) ? $this->clean ($this->s[$str]): false;
		}

		function clean($str, $mode = 0) {  // mode: 0 ALL, 1- slashes, html only, 2 - mysql only
			if(get_magic_quotes_gpc()  && ($mode<2)) {
				$str = stripslashes($str);
				$str = htmlentities ($str);
			}
			return $str;
		}

		function cleanIDS($ids) {
			$idl = explode (",", $ids);
			$b = true;
			$a = array();
			foreach ($idl as $id)
				if (intval(trim($id)))
					$a[] = intval(trim($id));
			return implode ("," ,$a);
		}

		function getAct ($act) { // call as :   getAct("getlist")
			return $act;
		}

		function compareAct ($hash, $act) {
			return ($hash==$act);
		}

		function isAct($st) {  // call as :   isAct("getlist")
			return $this->compareAct($this->act, $st);
		}

		function hash ($st) { // HASH functions
			return md5($st);
		}

		function date($ut = "", $format = "") {
			if ($ut)
				return $format ? date($format, $ut) : date("d/m/Y H:i", $ut);
			else 
				return "-- --- ---- --:--";
		}

		function finishAct ($msg = "") {
			if (is_array($msg))
				print json_encode($msg);
			else
				print $msg;
			exit;
		}

		function bindAct ($inact, $func) {
			if (!$inact) {
				$func();
			} else {
				if ($this->isAct($inact)) {
					$this->finishAct($func());
				} else
					return false;
			}
		}

		function getRate (){
			if ($r = $this->getRateEx()) {
				$this->c["internal_rate"] = $r;
				$this->c["internal_rate_time"] = time();
				$this->write();
			} else {
				$r = $this->c["internal_rate"];
			}			
			return $r;			
		}

		function getRateEx() {
			switch ($this->c["exchange_rate"]) {
				case "binance" : {
					$arr = json_decode(file_get_contents("https://api.binance.com/api/v1/ticker/allPrices"), true);
					$rate = -1;
					if (is_array($arr) && (count($arr)>0)) {
						foreach ($arr as $t) {
							if ($t["symbol"] == strtoupper($this->type)."USDT") {
								$rate = round($t["price"], 2);
								break;
							}
						}
						return $rate;
					} else {
						return -1;
					}
					break;
				}
				case "bitfinex" : {
					$elem = json_decode(file_get_contents("https://api.bitfinex.com/v2/tickers?symbols=t".strtoupper($this->type)."USD"), true);
					if ($elem)
						return round($elem[0]["1"], 2);
					else
						return -1;
					break;
				}
				case "bitstamp" : {
					$elem = json_decode(file_get_contents("https://www.bitstamp.net/api/v2/ticker/".strtolower($this->type)."usd"), true);
					if ($elem)
						return round($elem["bid"], 2);
					else
						return -1;
					break;
				}
			}
			return -1;
		}



	}

	function tableSettings ($tables) { // $tables - array of names of HTML tables for preparing
		global $currentpage, $perpage, $sortcolumn, $filters;
		foreach ($tables as $table) {
			$js_currentpage[$table] = isset($currentpage[$table]) ? $currentpage[$table] : "";
			$js_perpage[$table] = isset($perpage[$table]) ? $perpage[$table] : "";
			$js_filters[$table] = is_array($filters[$table]) ? $filters[$table] : array();
			$js_sortcolumn[$table]["field"] = isset($sortcolumn[$table]["field"]) ? $sortcolumn[$table]["field"] : "";
			$js_sortcolumn[$table]["direction"] = isset($sortcolumn[$table]["direction"]) ? $sortcolumn[$table]["direction"] : "";
		}
		return "
			var currentpage = ".json_encode($js_currentpage).";
			var perpage = ".json_encode($js_perpage).";
			var filters = ".json_encode($js_filters).";
			var sortcolumn = ".json_encode($js_sortcolumn)."\n\n";
	}

	function prepareTables ($t, $tables) {
		global $vars, $currentpage, $perpage, $sortcolumn, $pager;
		
		$currentpage = $perpage = $sortcolumn = $filters = array();

		foreach ($tables as $topic) {
			if (!$currentpage[$topic]) {
				if ($_SESSION[$t]["currentpage"][$topic]) {
					$currentpage[$topic] = $_SESSION[$t]["currentpage"][$topic];
				} else {
					$currentpage[$topic] = $_SESSION[$t]["currentpage"][$topic] = 1;
				}
			}
			if (!$filters[$topic]) {
				if ($_SESSION[$t]["filters"][$topic]) {
					$filters[$topic] = $_SESSION[$t]["filters"][$topic];
				} else {
					$filters[$topic] = $_SESSION[$t]["filters"][$topic] = 1;
				}
			}
			if (!$sortcolumn[$topic]["field"]) {
				if ($_SESSION[$t]["sortcolumn"]["field"][$topic]) {
					$sortcolumn[$topic]["field"] = $_SESSION[$t]["sortcolumn"]["field"][$topic];
					$sortcolumn[$topic]["direction"] = $_SESSION[$t]["sortcolumn"]["direction"][$topic];
				}
			}
			if (!$perpage[$topic]) {
				if ($_SESSION[$t]["perpage"][$topic]) {
					$perpage[$topic] = $_SESSION[$t]["perpage"][$topic];
				} else {
					$perpage[$topic] = $_SESSION[$t]["perpage"][$topic] = $pager[1];
				}
			}
			if ($vars->isAct ($topic)) {
				$currentpage[$topic] = $_SESSION[$t]["currentpage"][$topic] = $vars->cleanp("currentpage");
				$perpage[$topic] = $_SESSION[$t]["perpage"][$topic] = $vars->cleanp("perpage");
				$sortcolumn[$topic]["field"] = $_SESSION[$t]["sortcolumn"]["field"][$topic] = $vars->cleanp("sortcolumn");
				$sortcolumn[$topic]["direction"] = $_SESSION[$t]["sortcolumn"]["direction"][$topic] = $vars->cleanp("sortdirection");
			}
		}
	}

	function dirSize ($dir) {
	    $size = 0;
	    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
	        $size += is_file($each) ? filesize($each) : dirSize($each);
	    }
	    return $size;
	}

	function shortenString ($st, $maxchars, $mid = "...") {
		if (strlen($st)>$maxchars) {
			return substr($st, 0, intval($maxchars/2)).$mid.substr($st, - intval($maxchars/2));
		} else {
			return $st;
		}
	}

	function tail ($filepath, $lines = 1, $skip = 0, $adaptive = true) {
		$f = @fopen($filepath, "rb");
		if ($f === false) {
			return "File not exists or permission denied";
		}
		if (@flock($f, LOCK_SH) === false) {
			return "Can't lock file for exclusive reading";
			return false;
		}
		if (!$adaptive) {
			$buffer = 4096;
		} else {
    		$max = max($lines, $skip);
    		$buffer = ($max < 2 ? 64 : ($max < 10 ? 512 : 4096));
		}
		fseek($f, -1, SEEK_END);
		if (fread($f, 1) == "\n") {
    		if ($skip > 0) {
				$skip++; 
				$lines--;
			}
		} else {
			$lines--;
		}
		$output = '';
		$chunk = '';
		while (ftell($f) > 0 && $lines >= 0) {
			$seek = min(ftell($f), $buffer);
			fseek($f, -$seek, SEEK_CUR);
			$chunk = fread($f, $seek);
			$count = substr_count($chunk, "\n");
			$strlen = mb_strlen($chunk, '8bit');
			fseek($f, -$strlen, SEEK_CUR);
			if ($skip > 0) {
				if ($skip > $count) {
					$skip -= $count; 
					$chunk=''; 
				} else {
					$pos = 0;
					while ($skip > 0) {
						if ($pos > 0) {
							$offset = $pos - $strlen - 1;
						} else {
							$offset=0;
						}
						$pos = strrpos($chunk, "\n", $offset);
						if ($pos !== false) {
							$skip--; 
						} else {
							break;
						}
					}
					$chunk=substr($chunk, 0, $pos);
					$count=substr_count($chunk, "\n");
				}
			}
			if (strlen($chunk) > 0) {
      			$output = $chunk . $output;
      			$lines -= $count;
			}
		}
		while ($lines++ < 0) {
			$output = substr($output, strpos($output, "\n") + 1);
		}
		@flock($f, LOCK_UN);
		fclose($f);
		return trim($output);
	};

	date_default_timezone_set('Europe/Moscow');
	error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

	ini_Set("session.save_path", "/tmp");

	$vars = new variables ();

	$pager = array ("25", "50", "100", "500");

	$btc = new bitcoin($vars->c["btc_rpc_user"], $vars->c["btc_rpc_password"], $vars->c["btc_server_ip"], $vars->c["btc_server_port"]);  
?>﻿