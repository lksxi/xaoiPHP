<?php
// v2019/01/14

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义应用目录
define('APP_PATH','app');

// 检测PHP环境
if(APP_DEBUG && version_compare(PHP_VERSION,'5.4.0','<')) die('require PHP > 5.4.0 !');

// 入口
Xaoi::start();

// 配置函数
function C($k){
	static $_conf = array(
		'xaoi'	=> array(
			'tpl'		=> array(
				'cache'		=> 'runtime/cache/tpl'
			),
			'database'	=> array(
				'default' => [
					'mysql'	=> [
						'host'	    => '127.0.0.1',
						'username'  => 'root',
						'password'  => 'usbw',
						'database'  => 'test',
						'charset'   => 'UTF8',
						'prefix'    => ''
					]
				],
				'sqlite'
					=> array(
					'sqlite'	=> array(
						'file'		=> '/db/.xaoi.db',
						'prefix'    => ''
					)
				),
			),
			'sys'	=> array(
				'cookie'	=> 'LHZtpVJApBMx4EJeevn6GBhZLzvuOkVA',
				'host'	=> array(
					'127.0.0.1',
					'localhost',
				),
				'name'	=> array(
					'controller'	=> 'code',
					'model'			=> 'model',
					'view'			=> 'view'
				),
				'route'	=> array(
					'url'	=> array(
						'type'	=> 2,		// 0、普通模式	1、PATH_INFO	2、REWRITE
						'info'	=> 'r',		// 普通模式时的get变量名
						'space'	=> '/',		// 分隔符
						'suffix'=> '.html'	// 后缀
					),
					'is_default'	=> true,
					'access'	=> array('home','admin','mobile','weixin'),		// 允许访问的模块
					'deny'		=> array('common','runtime'),	// 拒绝访问的模块
					'default_name'	=> array(
						'module'		=> 'home',
						'controller'	=> 'index',
						'action'		=> 'index',
					),
				),
			),
		),
	);

	if(empty($k))return;
	$pos = strpos($k,'.');
	if($pos===false){
		$file = $k;
		$k = array();
	}else{
		$file = substr($k,0,$pos);
		$k = explode('.',substr($k,$pos+1));
	}

	if(!isset($_conf[$file])){
		$_conf[$file] = array();
		$path = _APP_.'/config/'.$file.'.php';
		if(is_file($path)){
			$_conf[$file] = include($path);
		}
		$path = _MODULE_.'/config/'.$file.'.php';
		if(is_file($path)){
			$_conf[$file] = array_merge($_conf[$file],include($path));
		}
	}

	$p = &$_conf[$file];
	switch(func_num_args()){
		case 1:
			for($i=0,$l=count($k);$i!=$l;++$i){
				if(!is_array($p))return;
				$p = &$p[$k[$i]];
			}
			return $p;
		break;
		case 2:
			for($i=0,$l=count($k);$i!=$l;++$i){
				if(!is_array($p))$p = array();
				$p = &$p[$k[$i]];
			}
			$r = $p;
			$p = func_get_arg(1);
			return $r;
		break;
	}
	return $p;
}

// 常用函数
	// 快速实例化模板
	function _tpl($d = null,$f = ''){
		$t = new Tpl($d);
		$t->display($f);
	}

	// 获取表选择函数或表对象
	function db(){
		switch(func_num_args()){
			case 0:
				$id = 'default';
				if(empty(Db::$_fn[$id])){
					$dbarr = C('xaoi.database');
					Db::$_fn[$id] = Db::connect($id,$dbarr[$id]);
				}
				$r_tab = '';
			break;
			case 1:
				$r_tab = func_get_arg(0);
				if($r_tab[0] === ':'){
					$id = substr($r_tab,1);
					$r_tab = '';
				}else{
					$id = 'default';
				}
				if(empty(Db::$_fn[$id])){
					$dbarr = C('xaoi.database');
					Db::$_fn[$id] = Db::connect($id,$dbarr[$id]);
				}
			break;
			case 2:
				$id = func_get_arg(0);
				if(empty(Db::$_fn[$id])){
					Db::$_fn[$id] = Db::connect($id,func_get_arg(1));
				}
				$r_tab = '';
			break;
		}
		return empty($r_tab)?Db::$_fn[$id]:Db::$_fn[$id]($r_tab);
	}

	// 调试输出
	function P($var){
		if(php_sapi_name() === 'cli'){
			echo "\n";
			if (is_null($var))var_dump(NUll);else print_r($var);
			echo "\n\n";
		}else{
			if(!defined('SET_UTF8')){
				define('SET_UTF8',true);
				header("Content-type: text/html; charset=utf-8");
				echo '<meta content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=0,minimal-ui" name="viewport" />'."\n";
			}
			echo '<pre style="position:relative;z-index:1000;padding:10px;border-radius:5px;background:#f5f5f5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;"'."\n".'>';
			if (is_bool($var))var_dump($var);else if (is_null($var))var_dump(NUll);else print_r($var);
			echo "\n</pre>\n\n";	
		}
	}

	// 调试输出并退出
	function _P($var){
		P($var);
		exit;
	}

	// 错误输出
	function E($ts1,$ts2 = ''){
		if(APP_DEBUG){
			$ts = $ts1;
		}else{
			$ts = $ts2;
		}
		if(php_sapi_name() === 'cli'){
			echo "\nerror:>>\n\n";
			if (is_null($ts))var_dump(NUll);else print_r($ts);
			echo "\n\n<<\n";
		}else{
			if(!defined('SET_UTF8')){
				define('SET_UTF8',true);
				header("Content-type: text/html; charset=utf-8");
				echo '<meta content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=0,minimal-ui" name="viewport" />'."\n";
			}
			echo '<title>出错了！</title><pre style="position:relative;z-index:1000;padding:10px;border-radius:5px;background:#fab9a3;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;"'."\n".'>';
			if (is_bool($ts))var_dump($ts);else if (is_null($ts))var_dump(NUll);else print_r($ts);
			echo "\n</pre>\n\n";
		}
	}

	// 错误输出并退出
	function _E($var){
		E($var);
		exit;
	}

	// 退出提示并跳转-需要自定义
	function _exit($s,$u=false){
		header("Content-type: text/html; charset=utf-8");
		if($u !== false)header('refresh:3;url='.(empty($u)?U('/'):$u));
		exit('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"><div style="font-size:24px;">'.$s.'</div>');
	}

	// 获取并过滤变量
	function I($_p){
		if(is_array($_p)){
			foreach($_p as &$v){
				$v = I($v);
			}
		}else{
			$tmp = explode('?',$_p,2);
			$zz = isset($tmp[1])?$tmp[1]:null;
			$tmp = explode('=',$tmp[0],2);
			$def = isset($tmp[1])?$tmp[1]:null;
			$tmp = explode('/',$tmp[0],2);
			$type = isset($tmp[1])?$tmp[1]:null;
			$a = explode('.',$tmp[0]);
			$key = array_shift($a);
			switch($key){
				case 'get':
					$p = &$_GET;
				break;
				case 'post':
					$p = &$_POST;
				break;
				case 'files':
					$p = &$_FILES;
				break;
				case 'request':
					$p = &$_REQUEST;
				break;
				case 'session':
					$p = &$_SESSION;
				break;
				case 'cookie':
					$p = &$_COOKIE;
				break;
				case 'server':
					$p = &$_SERVER;
				break;
				case 'globals':
					$p = &$GLOBALS;
				break;
				default:
					return;
				break;
			}
			for($i=0,$l=count($a);$i!=$l;++$i){
				if(!is_array($p)){
					break;
				}else{
					$p = &$p[$a[$i]];
				}
			}
			$_p = $p;
			if(is_null($_p)){
				if(is_null($def)){
					exit('input error');
				}else{
					$_p = $def;
				}
			}elseif(!is_null($zz)){
				if(1 !== preg_match($zz,(string)$_p)){
					if(is_null($def)){
						exit('input error');
					}else{
						$_p = $def;
					}
				}
			}
			switch($type){
				case 'i':
					$_p = (int)$_p;
				break;
				case 'f':
					$_p = (float)$_p;
				break;
				case 'd':
					$_p = (double)$_p;
				break;
				case 's':
					$_p = htmlspecialchars($_p);
				break;
				case 'b':
					$_p = (bool)$_p;
				break;
				case 'a':
					$_p = (array)$_p;
				break;
				case 'o':
					$_p = (object)$_p;
				break;
			}
		}
		return $_p;
	}

	// 获取url地址
	function U($url = '',$vars='',$suffix=true){
		$c = array('m'=>MODULE,'c'=>CONTROLLER,'a'=>ACTION);
		$info   =  parse_url($url);
		$url    =  !empty($info['path'])?$info['path']:ACTION;
		if(isset($info['fragment'])) {
			$anchor =   $info['fragment'];
			if(false !== strpos($anchor,'?')) {
				list($anchor,$info['query']) = explode('?',$anchor,2);
			}        
			if(false !== strpos($anchor,'@')) {
				list($anchor,$host)    =   explode('@',$anchor, 2);
			}
		}elseif(false !== strpos($url,'@')) {
			list($url,$host)    =   explode('@',$info['path'], 2);
		}
		$url_type = C('xaoi.sys.route.url.type');
		if(is_int($vars)) {
			if(in_array($vars,array(0,1,2)))$url_type = $vars;
			$vars = '';
		}elseif(is_string($vars) && $vars != '') {
			parse_str($vars,$vars);
		}elseif(!is_array($vars)){
			$vars = array();
		}
		if(isset($info['query'])) {
			parse_str($info['query'],$params);
			$vars = array_merge($params,$vars);
		}
		if($url){
			$r='';
			if(isset($host))$r=(is_ssl()?'https:// ':'http:// ').(empty($host)?$_SERVER["HTTP_HOST"]:$host);
			$root = __ROOT__;
			if(!empty($root))$r .= $root;
			$filename = substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'] ,'/')+1);
			switch($url_type){
				case 0:
					$r .= '/'.$filename.'?'.C('xaoi.sys.route.url.info').'=';
					$suffix = false;
				break;
				case 1:
					$r .= '/'.$filename.'/';
				break;
				case 2:
					$r .= '/';
				break;
			}
			if(strpos($url,'>')){
				list($show,$url) = explode('>',$url, 2);
			}
			if($url[0] != '/'){
				$urls = explode('/',$url);
				krsort($urls);
				$a = array('a','c','m');
			}else{
				$urls = explode('/',substr($url,1));
				$a = array('m','c','a');
				$c = array(
					'm'	=> C('xaoi.sys.route.default_name.module'),
					'c'	=> C('xaoi.sys.route.default_name.controller'),
					'a'	=> C('xaoi.sys.route.default_name.action')
				);
			}
			$urls = array_values($urls);
			for($i = 0;$i != 3;++$i){
				if(!empty($urls[$i]))$c[$a[$i]] = $urls[$i];
			}
			$space = C('xaoi.sys.route.url.space');
			$r .= $c['m'].$space.$c['c'].$space.$c['a'];

			if(!empty($vars)) {
				foreach ($vars as $var => $val){
					if('' !== trim($val))   $r .= $space . $var . $space . urlencode(trim($val));
				}      
			}

			if($suffix) {
				$r .= $suffix===true ?C('xaoi.sys.route.url.suffix') :$suffix;
			}

			if(isset($anchor))$r .= '#'.$anchor;

			if(!empty($show)){
				switch($show){
					case 'script':
						$r = '<script src="'.$r.'"></script>';
					break;
				}
			}
			return $r;
		}
	}

	// 输出到浏览器-U函数
	function _U(){
		return '<script src="'.__ROOT__.'/public/static/lib/u.js">'.json(array(
				'__root__'=>__ROOT__,
				'file'=>substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'] ,'/')+1),
				'url'=>C('xaoi.sys.route.url'),
				'is_default'=>C('xaoi.sys.route.is_default'),
				'path'=>array('m'=>MODULE,'c'=>CONTROLLER,'a'=>ACTION),
				'def'=>array(
					'm'	=> C('xaoi.sys.route.default_name.module'),
					'c'	=> C('xaoi.sys.route.default_name.controller'),
					'a'	=> C('xaoi.sys.route.default_name.action')
				)
			)).'</script>';
	}

	// 设置cookie
	function cookie(){
		switch(func_num_args()){
			case 0:
				return $_COOKIE;
			break;
			case 1:
				$k = func_get_arg(0);
				if(isset($_COOKIE[$k])){
					return $_COOKIE[$k];
				}elseif(is_null($k) || $k == ''){
					foreach($_COOKIE as $key => &$value){
						setcookie($key,null,null,'/');
					}
					$c = $_COOKIE;
					$_COOKIE = array();
					return $c;
				}
			break;
			case 2:
				$k = func_get_arg(0);
				$v = func_get_arg(1);
				if(!is_null($k)){
					if(is_null($v) || $v == ''){
						unset($_COOKIE[$k]);
						setcookie($k,null,null,'/');
					}else{
						$_COOKIE[$k] = $v;
						setcookie($k,$v,null,'/');		
					}
				}
			break;
			case 3:
				$k = func_get_arg(0);
				$v = func_get_arg(1);
				$o = func_get_arg(2);
				if(!is_null($k)){
					if(is_null($v) || $v == ''){
						unset($_COOKIE[$k]);
						setcookie($k,null,null,'/');
					}else{
						$_COOKIE[$k] = $v;
						if(is_numeric($o)){
							setcookie($k,$v,$o,'/');
						}elseif(is_array($o)){
							setcookie(
								$k,
								$v,
								!empty($o['expire']) && is_numeric($o['expire'])?$o['expire']:(time()+3600*24),
								!empty($o['path'])?$o['path']:'/',
								!empty($o['domain'])?$o['domain']:'',
								!empty($o['secure'])?$o['secure']:''
							);
						}
					}
				}
			break;
		}
	}

	// 设置session
	function session(){
		if(empty($_SESSION)){
			session_start();
		}
		switch(func_num_args()){
			case 0:
				return $_SESSION;
			break;
			case 1:
				$k = func_get_arg(0);
				if(is_array($k)){
					foreach($k as $key => &$value){
						$_SESSION[$key] = $value;	
					}
				}elseif(is_null($k)){
					session_unset();
					session_destroy();
				}elseif(!empty($k)){
					if($k === ':'){
						$k = MODULE;
					}elseif($k[0] === ':'){
						$k = MODULE.'.'.substr($k,1);
					}
					$k = explode('.',$k);
					$p = &$_SESSION;
					for($i=0,$l=count($k);$i!=$l;++$i){
						if(!is_array($p))return;
						$p = &$p[$k[$i]];
					}
					return $p;
				}
			break;
			case 2:
				$k = func_get_arg(0);
				$v = func_get_arg(1);
				if(!empty($k)){
					if($k === ':'){
						$k = MODULE;
					}elseif($k[0] === ':'){
						$k = MODULE.'.'.substr($k,1);
					}
					$k = explode('.',$k);
					$p = &$_SESSION;
					for($i=0,$l=count($k);$i!=$l;++$i){
						if(!is_array($p))$p=array();
						$p = &$p[$k[$i]];
					}
					$p = $v;
				}
			break;
		}
	}
	
	// 获取或修改文件
	function F()
	{
		switch(func_num_args()){
			case 1:
				$p = func_get_arg(0);
				if(!empty($p) && (is_file($p) || substr($p,0,4) == 'http'))return file_get_contents($p);
			break;
			case 2:
				$p = func_get_arg(0);		
				$v = func_get_arg(1);
				if(!empty($p) && !empty($v)){
					if(!is_string($v))$v = serialize($v);
					if(!is_dir(dirname($p)))mkdir(dirname($p),0777,true);
					return file_put_contents($p,$v);
				}
			break;
		}
	}

	// json编码
	function json($d,$is_obj = false){
		return json_encode($d,$is_obj?JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE:JSON_UNESCAPED_UNICODE);
	}

	// json编码-输出-退出
	function _json($d){
		exit(json($d));
	}

	// 是否ajax访问
	function is_ajax($is = false){
		if(is_referer($is) && !empty($_SERVER['HTTP_AJAX']) && $_SERVER['HTTP_AJAX'] === 'XAOI')return true;
		if($is)exit;
		return false;
	}

	// 是否指定域名访问
	function is_referer($is = false){
		if(!empty($_SERVER['HTTP_REFERER'])){
			$url = parse_url($_SERVER['HTTP_REFERER']);
			if(!empty($url['host']) && in_array($url['host'], C('xaoi.sys.host')))return true;
		}
		if($is)exit;
		return false;
	}

	// 获取url数据-file_get_contents
	function post($url,$data=' ',$cookie = ''){
		if(is_array($data)){
			$data = http_build_query($data);
			if(empty($data))$data=' ';
		}
		if(is_array($cookie)){
			foreach($cookie as $k => &$v){
				$v = $k.'='.$v;
			}
			$cookie = implode('; ',$cookie);
		}
		return file_get_contents($url,false,stream_context_create(array('http'=>array(
			'method'=>'POST',
			'header'=>
				'Content-type: application/x-www-form-urlencoded'."\r\n".
				($cookie != ''?('Cookie: '.$cookie."\r\n"):'').
				'Content-length: '.strlen($data)."\r\n",
			'content'=>$data))));
	}

	// 获取url数据-curl
	function _post($url, $data = array(),$cookie = ''){	
		if(is_array($cookie)){
			foreach($cookie as $k => &$v){
				$v = $k.'='.$v;
			}
			$cookie = implode(';',$cookie);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if(!empty($data)){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		if(!empty($cookie)){
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		$r = curl_exec($ch);
		curl_close($ch);
		return $r;
	}

	// 获取url数据-fsockopen-可异步
	function _fsockopen($url,$post = array(),$exit = false,$referer = ''){
		$par = parse_url($url);
		if($par['scheme'] === 'http' || $par['scheme'] === 'https'){
			if( $par['scheme'] === 'https'){
				$ssl = 'ssl:// ';
				if(!isset($par['port']))$par['port'] = 443;
			}else{
				$ssl = '';
				if(!isset($par['port']))$par['port'] = 80;
			}

			if(isset($par['path'])){
				$path = substr($url,strpos($url,'/',strpos($url,$par['host'])+strlen($par['host'])));
			}else{
				$path = '/';
			}

			if($post) {
				if(is_array($post))
				{
					$post = http_build_query($post);
				}
				$out = "POST ".$path." HTTP/1.0\r\n";
				$out .= "Accept: */*\r\n";
				if(!empty($referer))$out .= "Referer: ".$referer."\r\n";
				$out .= "Accept-Language: zh-cn\r\n";
				$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$out .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
				$out .= "Host: ".$par['host']."\r\n";
				$out .= 'Content-Length: '.strlen($post)."\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Cache-Control: no-cache\r\n\r\n";
				$out .= $post;
			} else {
				$out = "GET ".$path." HTTP/1.0\r\n";
				$out .= "Accept: */*\r\n";
				if(!empty($referer))$out .= "Referer: ".$referer."\r\n";
				$out .= "Accept-Language: zh-cn\r\n";
				$out .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
				$out .= "Host: ".$par['host']."\r\n";
				$out .= "Connection: Close\r\n";
				$out .= "Cache-Control: no-cache\r\n\r\n";
			}

			$fp = fsockopen($ssl.$par['host'], $par['port'], $errno, $errstr, 30);   
			if(!$fp)return false;

			fwrite($fp, $out);
			if($exit)return;
			$r = '';
			while (!feof($fp)) {
				$r .= fgets($fp, 128);
			}
			fclose($fp);
			return $r;
		}
	}

	// 批量获取url数据
	function posts($arr,$fn = null){
		$chs = array();
		foreach($arr as $url => &$v){
			$chs[$url] = curl_init();
			$ch = &$chs[$url];
			curl_setopt($ch, CURLOPT_URL, $url);
			if(!empty($v['data'])){
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $v['data']);
			}
			if(!empty($v['cookie'])){
				if(is_array($v['cookie'])){
					foreach($v['cookie'] as $k2 => &$v2){
						$v2 = $k2.'='.$v2;
					}
					$v['cookie'] = implode(';',$v['cookie']);
				}
				curl_setopt($ch, CURLOPT_COOKIE, $v['cookie']);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		}
		unset($ch);
		unset($v);
		$mh = curl_multi_init();
		foreach($chs as &$ch){
			curl_multi_add_handle($mh, $ch);
		}
		unset($ch);

		$active = null; 
		do{
			while(($mrc = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM);
			if($mrc != CURLM_OK)break;
			while ($done = curl_multi_info_read($mh)) {
				$url = array_search($done['handle'],$chs);
				$arr[$url]['info'] = curl_getinfo($done['handle']);
				$arr[$url]['error'] = curl_error($done['handle']);
				$arr[$url]['result'] = curl_multi_getcontent($done['handle']);
				if(is_callable($fn))$fn($arr[$url]);
				curl_multi_remove_handle($mh, $done['handle']);
				curl_close($done['handle']);
			}
			if($active > 0)curl_multi_select($mh);
		}while($active);
		curl_multi_close($mh);
		return $arr;
	}

	// 获取当前模块下对象
	function _new($c,$s = null){
		if(strpos($c,':') === false){
			$class = '\\'.str_replace('/','\\',$c);
		}else{
			list($m,$n) = explode(':',$c);
			$class = '\\'.(empty($m)?MODULE:$m).'\\'.str_replace('/','\\',$n);
		}
		return is_null($s)?new $class():new $class($s);
	}

	// 是否https访问
	function is_ssl() {
		if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
			return true;
		}elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
			return true;
		}
		return false;
	}

	// 是否手机访问
	function ismobile(){
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) return true; if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT']) return true; if (isset ($_SERVER['HTTP_VIA'])) return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false; if (isset ($_SERVER['HTTP_USER_AGENT'])) { $clientkeywords = array( 'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile' ); if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) { return true; } } if (isset ($_SERVER['HTTP_ACCEPT'])) { if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) { return true; } } return false;
	}

	// url-base64
	function url_base64_encode($string) {
		$data = base64_encode($string);
		$data = str_replace(array('+','/','='),array('-','_',''),$data);
		return $data;
	}

	function url_base64_decode($string) {
		$data = str_replace(array('-','_'),array('+','/'),$string);
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}

// 入口
final class Xaoi{
	static function start(){
		if(APP_DEBUG){
			ini_set('error_log', __DIR__ . '/error_log.txt');
			ini_set('display_errors','On');
		}else{
			ini_set('display_errors','Off');
		}
		date_default_timezone_set('PRC');
		
		// 设置自动加载
		spl_autoload_register(function ($class) {
			$file = _APP_ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
			if(is_file($file)){
				require_once $file;
				return true;
			} else {
				_E('Class: '.$class.' 不存在 file:'.$file);
			}
			return false;
		});

		$route = self::route();

		$root = rtrim(dirname(rtrim($_SERVER['SCRIPT_NAME'],'/')),'/');

		define('_ROOT_',__DIR__);
		define('__ROOT__',($root=='/' || $root=='\\')?'':$root);

		define('MODULE',$route['m']);
		define('CONTROLLER',$route['c']);
		define('ACTION',$route['a']);

		define('_APP_',_ROOT_.'/'.APP_PATH);
		define('_MODULE_',_APP_.'/'.MODULE);

		$type = C('xaoi.sys.route.url.type');
		define('__MODULE__',__ROOT__.'/'.($type === 2?'':(basename(__FILE__).$type === 1?'/':(C('xaoi.sys.route.url.info').'='))).MODULE);
		define('__CONTROLLER__',__MODULE__.'/'.CONTROLLER);
		define('__ACTION__',__CONTROLLER__.'/'.ACTION);

		define('__STATIC__',__ROOT__.'/public/static');
		
		// 初始化目录		
		if(APP_DEBUG)self::create_dir();

		// 组装
		$class = '\\'.MODULE.'\\'.C('xaoi.sys.name.controller').'\\'.CONTROLLER;
		if(class_exists($class))$o = new $class(); else _E('找不到控制器:'.CONTROLLER);
		if(!method_exists($o,ACTION))_E('没有在 "'.CONTROLLER.'" 找到 "'.ACTION.'" 方法');

		try{
			$method = new \ReflectionMethod($class,ACTION);
		}catch(Exception $e){
			_E('没有在 "'.CONTROLLER.'" 找到 "'.ACTION.'" 方法');
		}
		if(!$method->isPublic())_E('无权访问!');
		$arr = array();
		$params = $method->getParameters();
		foreach ($params as $key => $param)
		{
			if(isset($_GET[$param->getName()])){
				$arr[$param->getName()] = $_GET[$param->getName()];
			}elseif($param->isDefaultValueAvailable()){
				$arr[$param->getName()] = $param->getDefaultValue();
			}else{
				_E('参数错误或者未定义:'.$param->getName());
			}
		}

		$r = call_user_func_array(array($o, ACTION),$arr);
		if(is_string($r))exit($r);
	}
	
	static private function route(){
		$c = C('xaoi.sys.route.default_name');
		$r = array(
			'm'	=> $c['module'],
			'c'	=> $c['controller'],
			'a'	=> $c['action'],
		);
		
		$purl = null;
		if(defined('START_ROUTE')){
			$purl = START_ROUTE;
		}elseif(php_sapi_name() === 'cli' && !empty($_SERVER['argv']) && !empty($_SERVER['argv'][1])){
			$purl = $_SERVER['argv'][1];
			for($i = 2,$s = count($_SERVER['argv']);$i<$s;++$i){
				if(!empty($_SERVER['argv'][$i]) && substr($_SERVER['argv'][$i],0,1) === ':'){
					$name = substr($_SERVER['argv'][$i],1);
					if(!empty($name)){
						echo $name.":\n";
						$tmp = explode('&',trim(fgets(STDIN)));
						$value = array();
						foreach($tmp as &$v)
						{
							$t = explode('=',$v,2);
							$value[$t[0]] = empty($t[1])?'':$t[1];
						}
						if(isset($GLOBALS[$name])){
							$GLOBALS[$name] = $value;
						}else{
							C($name,$value);
						}
					}	
				}
			}
		}else{
			$info = C('xaoi.sys.route.url.info');
			if(!empty($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '/'){
				$purl = &$_SERVER['PATH_INFO'];
				if($p = strripos($purl,C('xaoi.sys.route.url.suffix')))$purl = substr($purl,0,$p);
			}elseif(!empty($_GET[$info]) && $_GET[$info] !== '/'){
				$purl = &$_GET[$info];
			}
		}
		if(!is_null($purl) && $purl !== ''){
			if($purl[0] == '/')$purl = substr($purl,1);
			$info = explode(C('xaoi.sys.route.url.space'),$purl);
			if(!empty($info[0])){
				if(C('xaoi.sys.route.deny') && in_array($info[0], C('xaoi.sys.route.deny'))){
					header('HTTP/1.1 404 Not Found');
					header('Status:404 Not Found');
					exit;
				}
				if(C('xaoi.sys.route.access') && !in_array($info[0], C('xaoi.sys.route.access'))){
					if(C('xaoi.sys.route.is_default')){
						array_unshift($info,'');
					}else{
						header('HTTP/1.1 404 Not Found');
						header('Status:404 Not Found');
						exit;
					}
				}else{
					$r['m'] = strtolower(trim($info[0]));
				}
			}
			if(!empty($info[1]))$r['c'] = strtolower(trim($info[1]));
			if(!empty($info[2]))$r['a'] = strtolower(trim($info[2]));

			$count = count($info)-3;
			if($count > 0){
				for($i = 0,$s = intval($count/2)+$count%2;$i!=$s;++$i){
					$_GET[$info[$i*2+3]]=isset($info[$i*2+1+3])?$info[$i*2+1+3]:'';
				}
			}
		}
		return $r;
	}

	static private function unzip_file($file, $destination){
		$zip = new ZipArchive();
		if ($zip->open($file) !== TRUE)_E('Could not open archive');
		$zip->extractTo($destination);
		$zip->close();
	}

	static private function create_dir(){
		$index = _APP_.'/'.C('xaoi.sys.route.default_name.module').'/'.C('xaoi.sys.name.controller').'/index.php';
		if(!is_file($index)){
			$tmp = tempnam(__DIR__,'tmp');
			F($tmp,gzuncompress(base64_decode('eNoL8GZmEWEAgdqGxz4MSIAFiBMLCvQD4Cra0VRwQlVk5OemIilDN4gPWVlyfgpCLQfDbKDawucuD4uAvBIgFsdQm5mXklqhV5BRoHtqI2eLgYTDQ7sJspdmluzc/YiFVXwj50dx5lksixQ61iZ36C2cuqOnI3nFC237OImbZ5KWuDKe4+JUMWY9cklFIEGl+KFMoFXjroMdMUunTltq9MvxVF7d5iurWwOPW+Wf3tB/957/l9P2dv8/y3+O+DBH1+0tJ24vsQNxQWlSTmYyAX9DFRWXJJbgVSuEoTYnMwkpnMqLp/vM2KE6YRqQV8XIwCCBVX1JQY5eVvHagN18hwx4/ohe0ZwWULEmlD0iQusjYyjbZBbTII/VVwSdMv/NFVnVZ3Cg6PDc9zn3V9rJ85ua+91aUFLK6zfppYxzR4SifFV2h+hsJq+sswdWbuc1i1Y0ehe3Q7xJdx23y9WTBZf7ftyVvjTT87Vrm8c/wb3LHtzNurdmQoFMspeQ57bKumvGkyVqvu87/9M2sepFvWWw4fxLClP9Uf2isezjox1MDAyzWBkYxLD6pRTkkxDf4tkGAn+120qsViTcfJKRpm5bZpVb7FGstM49ITH5c6rnjZcM// dqMx77aik+YV7Z/duxb095vVIKy4y0EMw853NGoqa+8p+rtppK+bbUmOd7AkKOKa2dwvOecdbHpOXfiut9Ly968Oj2+/n2HgIXw3d8ktvbPH3yM47j27a+VWjfkzt1ReWCq6dOvnOaZVKox7yY1ftSzAOnVFlPVZn5AZGpK4venDczXJeesUtBs3WxOde0ON7FxQt0VjBfD/UQXGX64vwLvlu2Vzx2xHx3X7j/HUv4Vo205+uMn9R0eU965uYa3B+ZzSq8RXXi0tyv4ge2eSr3Rp2RffpTZcb5UPMPaeFxNlbVQr6O295ekrm2YENX9L9/cyye250UkdoWFLdlUmyD01bWYnlVTefLC41mWHAGSfl6vP2V4qPxtnSyp7nq2Qq30J+LXKLeyDixrjdqE9T/t+ek+c/Uh4lywhkb/NnecHKf+JD2dv3h1DeF0QHPbvIzKwjcyH6QyZCkw7OsIWwi66+i319NE6s3buCc8SVJ/M3eoKtcC/nMM5bV9+SZLdK5miZ8wnvVo72HM3SNVv0Lk5k748nmp16/9XKmX1Q5u6P71TpDp5lLSnIv/J2+weeTgtAcWebHT+9XPvz07UegfPxGvsdiUxI/R0i9aX/Gonh4YqDV40TB/u6a3E/3map6fXcYbLUttk1+cuuIdc4jrm3J5W91Vmw4WrYspHMzb3l2kbWsOovD4tjyWTes+m76OuZWOhWv/3/88Py7LiZnr4vcdndZov+mxfXV6f47N9zeaX5533h8ax5XbWuqkTVDn7+kbMWaDRuuK6RlGlrzzGqv9r2yM/XO3+P2N5ItypUubdmbe4e/9uQFJfPfMrxfmL+aGFz1nLRvfdFhxV/vTMt27SxY// Cv7p+E3Tvk2Q3efbC8c2dP+53bVYaZm4zSqvy/N6l/uHVYYmXxwU4NfZUf7AHejEz2DLiKVRUoSwBKg8o7LgYFMJsRmLPZjB+mLBG6wgijf+49DaaRTcUsipFNVUIuRZGNTjhcmgcyCkZjMxqzGEM22hOjgEY2P3nqY7B5LcHcpdjNx1bkw8wHmVOKuwJAtkit9EAryGAYTYxH2FE8IsMIL3WQDeaPegE2CEaTHkKOjOjFGaXmC6GYn8uIrbhEtiN1zyuwmTA6S+EcRixgVijIsTCXEXf1gmwTQ2yk4ZlblwnahFrcI9uUyYSr8CfOHlY2SPLnZMhkZmAIZQXxAC/A8Po=')));
			self::unzip_file($tmp,__DIR__);
			unlink($tmp);
		}
	}
}

// 数据库
class Db{
	static public $_fn = [];
	static private $conn = [];
	private $db,$type,$conf,$id,$tab;
	private function __construct($id,$name){
		$this->type	= self::$conn[$id]['type'];
		$this->conf	= self::$conn[$id]['conf'];
		$this->db	= self::$conn[$id]['link'];
		$this->id	= $id;
		$this->tab	= $name;
	}

	static function connect($id,$conf){
		$type = key($conf);$c = $conf[$type];
		self::$conn[$id] = [];
		self::$conn[$id]['type'] = $type;
		switch($type){
			case 'mysql':
				$h = explode(':',$c['host'],2);
				try {
					self::$conn[$id]['link'] = @new \PDO('mysql:host='.$h[0].';port='.(isset($h[1])?$h[1]:3306).';dbname='.$c['database'],$c['username'],$c['password']);
				} catch(PDOException $e) {
					_E('Could not connect to the database:<br/>' . $e,'database link error');
				}
			break;
			case 'sqlite':
				try {
					self::$conn[$id]['link'] = @new \PDO('sqlite:'.$c['file']);
				} catch(PDOException $e) {
					_E('Could not connect to the database:<br/>' . $e,'database link error');
				}
			break;
			case 'mongodb':
				$def = [
					'host'	    => '127.0.0.1',
					'port'      => '27017',
					'username'  => '',
					'password'  => '',
					'database'  => 'test',
					'prefix'    => ''
				];
				$c += $def;
				$str = 'mongodb:// ';
				if(!empty($c['username'])){
					$str .= $c['username'];
					if(!empty($c['password'])){
						$str .= ':'.$c['password'];
					}
					$str .= '@';
				}
				$str .= $c['host'].':'.$c['port'];
				self::$conn[$id]['link'] = new \MongoDB\Driver\Manager($str);
			break;
		}
		self::$conn[$id]['conf'] = $c;
		return function($name = '')use($id){
			static $dbs = [];
			if(empty($dbs[$name]))$dbs[$name] = new self($id,$name);
			return $dbs[$name];
		};
	}

	// pdo
		function begin(){
			return $this->db->beginTransaction();
		}

		function commit(){
			return $this->db->commit();
		}

		function rollBack(){
			return $this->db->rollBack();
		}

		function exec($sql){
			return $this->db->exec($sql);
		}

		function _query($sql,$size = -1){
			$result = $this->db->query($this->filter($sql));
			if(!$result)return [];
			return $this->stmp_fetch($result,$size);
		}

		function _prepare($sql,$bind = [],$size = -1){
			$stmt = $this->db->prepare($sql);
			if(!$stmt->execute($bind))_E('database select error sql:'."\n".$sql,'database select error!');
			return $this->stmp_fetch($stmt,$size);
		}

		function is_table($name){
			return count($this->_query('SHOW TABLES LIKE "'.$name.'"')) === 1;
		}

		private function p_tab(){
			return '`'.$this->conf['prefix'].$this->tab.'`';
		}

		private function filter($sql){
			return str_replace('{pre}', $this->conf['prefix'], $sql);
		}

	// mongodb
		private function m_tab(){
			return $this->conf['database'].'.'.$this->conf['prefix'].$this->tab;
		}

		function get_id(){
			$tab = $this->tab;
			$this->tab = 'ids';
			$d = $this->get(['tab'=>$tab],'_id,addid',1);
			$addid = 1;
			if(empty($d)){
				$this->add(['tab'=>$tab,'addid'=>$addid]);
			}else{
				$addid = $d['addid'] + 1;
				$this->set(['_id'=>$d['_id']],['addid'=>$addid]);
			}
			$this->tab = $tab;
			return $addid;
		}

	// all
		function get(){
			$where = [];
			$field = '';
			$group = '';
			$order = '';
			$limit = '';
			switch(func_num_args()){
				case 1:
					if(is_int(func_get_arg(0))){
						$limit = func_get_arg(0);
					}else{
						$where = func_get_arg(0);
					}
				break;
				case 2:
					if(is_int(func_get_arg(1))){
						list($where,$limit) = func_get_args();
					}else{
						list($where,$field) = func_get_args();
					}
				break;
				case 3:
					if(is_int(func_get_arg(2))){
						list($where,$field,$limit) = func_get_args();
					}else{
						list($where,$field,$order) = func_get_args();
					}
				break;
				case 4:
					if(is_int(func_get_arg(3))){
						list($where,$field,$order,$limit) = func_get_args();
					}else{
						list($where,$field,$group,$order) = func_get_args();
					}
				break;
				case 5:
					list($where,$field,$group,$order,$limit) = func_get_args();
				break;
			}
			switch($this->type){
				case 'mysql':
				case 'sqlite':
					$sql = ['select','field'=>'*','from',$this->p_tab(),'where'=>'','group'=>'','order'=>'','limit'=>''];
					$sql_bind = [];
					if(!empty($field)){
						$sql['field'] = '`'.implode('`,`',explode(',',$field)).'`';
					}
					if(!empty($where)){
						$str = [];
						foreach($where as $k => $v){
							$lis = explode(':',$k,2);
							$k = $lis[0];
							$k2 = isset($lis[1])?$lis[1]:'';

							if(is_array($v)){
								$ins = [];
								foreach($v as $v2){
									$ins[] = '?';
									$sql_bind[] = $v2;
								}
								$str[] = '`'.$k.'` in('.implode(',',$ins).')';
							}else{
								if(strpos($k2,'{val}')){
									$str[] = '`'.$k.'` '.str_replace('{val}','?',$k2);
								}elseif(is_string($k2) && $k2 != ''){
									$str[] = '`'.$k.'` '.$k2.' ?';
								}else{
									$str[] = '`'.$k.'` = ?';
								}
								$sql_bind[] = $v;
							}
						}
						$sql['where'] = 'where '.implode(' and ',$str);
					}
					if(!empty($group)){
						if(is_string($group))
							$sql['group'] = 'group by ' . $group;
						else if(is_array($group)){
							$sql['group'] = 'group by `'.implode('`,`',$group).'`';
						}
					}
					if(!empty($order)){
						if(is_string($order))
							$sql['order'] = 'order by ' . $order;
						else if(is_array($order)){
							$str = [];
							foreach($order as $k => $v){
								$str[] = '`'.$k.'` '.($v?'desc':'asc');
							}
							$sql['order'] = 'order by '.implode(',',$str);
						}
					}
					if(!empty($limit)){
						if(is_numeric($limit)){
							$start	= $limit;
						}else{
							$start	= (int)$limit[0];
							$size	= (int)$limit[1];
						}
						$sql['limit'] = 'limit ' . $start . (!empty($size)? ','.$size: '');
					}

					$stmt = $this->db->prepare(implode(' ',$sql));
					$stmt->execute($sql_bind);
					
					if($limit == 1){
						return $stmt->fetch(PDO::FETCH_ASSOC);
					}else{
						return $stmt->fetchAll(PDO::FETCH_ASSOC);
					}
				break;
				case 'mongodb':
					$op = [];
					if(!empty($where['_id']) && is_string($where['_id']))$where['_id'] = new \MongoDB\BSON\ObjectId($where['_id']);
					if(!empty($field)){
						$op['projection'] = [];
						$sp = explode(',',$field);
						foreach($sp as $v){
							$op['projection'][$v] = 1;
						}
					}
					if(!empty($group)){
					
					}
					if(!empty($order)){
						if(is_string($order))
							$op['sort'] = [$order=>1];
						else if(is_array($order)){
							$op['sort'] = [];
							foreach($order as $k => $v){
								$op['sort'] = $v?-1:1;
							}
						}
					}
					if(!empty($limit)){
						if(is_numeric($limit)){
							$op['limit'] = $limit;
						}else{
							$op['skip'] = $limit[0];
							$op['limit'] = $limit[1];
						}
					}
					$query = new \MongoDB\Driver\Query($where, $op);
					$list = $this->db->executeQuery($this->m_tab(),$query)->toarray();
					if($limit == 1 && count($list) !== 0){
						$a = [];
						if(is_object($list[0]->_id)){
							$a['_id'] = (string)$list[0]->_id;
							unset($list[0]->_id);
						}
						foreach($list[0] as $k => $v){
							$a[$k] = $v;
						}
						return $a;
					}
					$r = [];
					foreach ($list as $d) {
						$a = [];
						if(is_object($d->_id)){
							$a['_id'] = (string)$d->_id;
							unset($d->_id);
						}
						foreach($d as $k => $v){
							$a[$k] = $v;
						}
						$r[] = $a;
					}
					return $r;
				break;
			}
		}

		function sum($where,$field){

		}

		function count($where,$field){

		}

		function add($d){
			switch($this->type){
				case 'mysql':
				case 'sqlite':
					$sql = ['insert','into',$this->p_tab(),'field'=>'','values','value'=>''];
					$field = [];
					$str = [];
					$bind = [];
					foreach($d as $k => $v){
						$field[] = $k;
						$str[] = '?';
						$bind[] = $v;
					}
					$this->sql['field'] = '(`'.implode('`,`',$field).'`)';
					$this->sql['value'] = '('.implode(',',$str).')';

					$sql = $this->filter(implode(' ',$this->sql));
					$stmt = $this->db->prepare($sql);
					if(!$stmt)_E('sql error:'.$sql);
					$stmt->execute($bind);
					$stmt = null;
					return $this->db->lastInsertId();
				break;
				case 'mongodb':
					$bulk = new \MongoDB\Driver\BulkWrite;
					$bulk->insert($d);
					$data = $this->db->executeBulkWrite($this->m_tab(), $bulk);
					return $data->getInsertedCount();
				break;
			}
		}

		function set(){
			$where = [];
			$set = '';
			switch(func_num_args()){
				case 0:
					_E('database set empty!');
				case 1:
					$set = func_get_arg(0);
				break;
				case 2:
					$where = func_get_arg(0);
					$set = func_get_arg(1);
				break;
			}
			switch($this->type){
				case 'mysql':
				case 'sqlite':
					$sql = ['update',$this->p_tab(),'set','set'=>'','where'=>''];
					$sql_bind = [];
					$str = [];
					foreach($set as $k => $v){
						$str[] = '`'.$k.'`=?';
						$sql_bind[] = $v;
					}
					$sql['set'] = implode(',',$str);
					if(!empty($where)){
						$str = [];
						foreach($where as $k => $v){
							$lis = explode(':',$k,2);
							$k = $lis[0];
							$k2 = isset($lis[1])?$lis[1]:'';

							if(is_array($v)){
								$ins = [];
								foreach($v as $v2){
									$ins[] = '?';
									$sql_bind[] = $v2;
								}
								$str[] = '`'.$k.'` in('.implode(',',$ins).')';
							}else{
								if(strpos($k2,'{val}')){
									$str[] = '`'.$k.'` '.str_replace('{val}','?',$k2);
								}elseif(is_string($k2) && $k2 != ''){
									$str[] = '`'.$k.'` '.$k2.' ?';
								}else{
									$str[] = '`'.$k.'` = ?';
								}
								$sql_bind[] = $v;
							}
						}
						$sql['where'] = 'where '.implode(' and ',$str);
					}
					$stmt = $this->db->prepare(implode(' ',$sql));
					$stmt->execute($sql_bind);
					$r = $stmt->rowCount();
					$stmt = null;
					return $r;
				break;
				case 'mongodb':
					if(!empty($where['_id']) && is_string($where['_id']))$where['_id'] = new \MongoDB\BSON\ObjectId($where['_id']);
					$bulk = new \MongoDB\Driver\BulkWrite;
					$bulk->update($where,['$set'=>$set],['multi'=>true,'upsert'=>false]);
					$d = $this->db->executeBulkWrite($this->m_tab(), $bulk);
					return $d->getModifiedCount();
				break;
			}
		}

		function del($where){
			switch($this->type){
				case 'mysql':
				case 'sqlite':
					$sql = ['delete','from',$this->p_tab(),'where'=>''];
					$sql_bind = [];
					$str = [];
					foreach($where as $k => $v){
						$lis = explode(':',$k,2);
						$k = $lis[0];
						$k2 = isset($lis[1])?$lis[1]:'';

						if(is_array($v)){
							$ins = [];
							foreach($v as $v2){
								$ins[] = '?';
								$sql_bind[] = $v2;
							}
							$str[] = '`'.$k.'` in('.implode(',',$ins).')';
						}else{
							if(strpos($k2,'{val}')){
								$str[] = '`'.$k.'` '.str_replace('{val}','?',$k2);
							}elseif(is_string($k2) && $k2 != ''){
								$str[] = '`'.$k.'` '.$k2.' ?';
							}else{
								$str[] = '`'.$k.'` = ?';
							}
							$sql_bind[] = $v;
						}
					}
					$sql['where'] = 'where '.implode(' and ',$str);
					$stmt = $this->db->prepare(implode(' ',$sql));
					$stmt->execute($sql_bind);
					$r = $stmt->rowCount();
					$stmt = null;
					return $r;
				break;
				case 'mongodb':
					if(!empty($where['_id']) && is_string($where['_id']))$where['_id'] = new \MongoDB\BSON\ObjectId($where['_id']);
					$bulk = new \MongoDB\Driver\BulkWrite;
					$bulk->delete($where);
					$d = $this->m->executeBulkWrite($this->m_tab(), $bulk);
					return $d->getDeletedCount();
				break;
			}
		}

		function close(){
			unset(self::$_fn[$this->id]);
			unset(self::$conn[$this->id]);
		}
}

// 模板引擎
class Tpl{
	private $_include = array();
	private $_include_js = array();
	private $_include_path = array();
	private $var = array();
	private $assign = array();

	public function __construct($d = null){
		if(is_array($d)){
			$this->assign = $d;
		}else if(!empty($d)){
			$t->assign('D',$d);
		}

		$tpl_switch = C('xaoi.tpl.mobile');

		if(!empty($tpl_switch['open']) && $tpl_switch['open']){
			$this->tpl_switch = array(
				'pc'	=> empty($tpl_switch['pc'])?'pc':$tpl_switch['pc'],
				'mobile'	=> empty($tpl_switch['mobile'])?'mobile':$tpl_switch['mobile']
			);
		}
	}

	public function assign($k){
		switch(func_num_args()){
			case 1:
				if(is_array($k)){
					array_merge($this->assign,$k);
				}
			break;
			case 2:
				$this->assign[$k] = func_get_arg(1);
			break;
		}
	}

	public function cache($method,$path,$name,$fn,$is = false){
		if(is_callable($fn)){
			list($tmp,$a) = explode('::',$method);
			list($m,$tmp,$c) = explode('\\',$tmp);

			$f = '/public/data/'.$m.'/'.$c.'/'.$a.'/'.$path.'.js';
			$file = _ROOT_.$f;
			$is_cache = false;
			if(is_file($file)){
				$filetime = filemtime($file);
				if(is_callable($is)){
					if($is($filetime) == true)$is_cache = true;
				}
			}else{
				$is_cache = true;
			}
			
			if($is_cache){
				$str = 'typeof GLOBAL.data["'.$m.'"] !== "object"&&(GLOBAL.data["'.$m.'"]={});'.
						'typeof GLOBAL.data["'.$m.'"]["'.$c.'"] !== "object"&&(GLOBAL.data["'.$m.'"]["'.$c.'"]={});'.
						'typeof GLOBAL.data["'.$m.'"]["'.$c.'"]["'.$a.'"] !== "object"&&(GLOBAL.data["'.$m.'"]["'.$c.'"]["'.$a.'"]={});'.
						'GLOBAL.data["'.$m.'"]["'.$c.'"]["'.$a.'"]["'.$name.'"] = '.json($fn()).';';
				$this->F($file,$str);
				$filetime = filemtime($file);
			}
			return __ROOT__.$f.'?v='.$this->dec62($filetime);
		}
	}

	public function tpl($str,$a = '<\?',$b = '\?>'){
		$this->var = array(
			'root'			=> __ROOT__,
			'static'		=> __STATIC__,
			'tool'			=> __ROOT__.'/public/tool',
			'lib'			=> __ROOT__.'/public/static/lib'
		);
		$d = $this->str_cutting($str,$a,$b);
		$r = '';
		foreach($d as &$v){
			$r.=$v[0].$this->code($v[1]);
		}
		return '<?php return function(){extract(func_get_arg(0),EXTR_PREFIX_SAME,"D");?>'.$r.'<?php };?>';
	}

	public function display($file = ''){
		$tpl = C('xaoi.tpl');

		$f = array();
		$f['m'] = MODULE;
		$f['view'] = C('xaoi.sys.name.view');
		$f['c'] = CONTROLLER;
		$f['a'] = ACTION;

		if(!empty($file)){
			$a = array('a','c','m');
			$file = explode('/',$file);
			krsort($file);
			$file = array_values($file);
			for($i = 0;$i != 3;++$i){
				if(!empty($file[$i]))$f[$a[$i]] = $file[$i];
			}
		}
		$path = _APP_.'/'.implode('/',$f).C('xaoi.sys.route.url.suffix');
		$cache = _APP_.'/'.$tpl['cache'].'/'.$f['m'].'/'.$f['c'].'/'.$f['a'].'.tpl.php';

		if(!is_file($path))_E('template file: '.$f['m'].'/'.$f['c'].'/'.$f['a'].' does not exist');

		if(is_file($cache)){
			$_isf = true;
			$_f = @fopen($cache, "r");
			$_d = unserialize(substr(fgets($_f),8));
			fclose($_f);
		}else{
			$_isf = false;
			$_d = array(array(),array());
		}

		if(!$_isf || !$this->checkCache($_d)){
			$this->var = array(
				'host'			=> (is_ssl()?'https:// ':'http:// ').$_SERVER["HTTP_HOST"],
				'root'			=> __ROOT__,
				'static'		=> __STATIC__,
				'module'		=> __STATIC__.'/'.$f['m'],
				'controller'	=> __STATIC__.'/'.$f['m'].'/'.$f['c'],
				'this'			=> __STATIC__.'/'.$f['m'].'/'.$f['c'].'/'.$f['a'],
				'tool'			=> __ROOT__.'/public/tool',
				'lib'			=> __ROOT__.'/public/static/lib',
				'm'				=> $f['m'],
				'c'				=> $f['c'],
				'a'				=> $f['a'],
				'_m'			=> __STATIC__.'/'.$f['m'],
				'_c'			=> __STATIC__.'/'.$f['m'].'/'.$f['c'],
				'_a'			=> __STATIC__.'/'.$f['m'].'/'.$f['c'].'/'.$f['a'],
			);
			$str = $this->compile($path);
			$_d[1] = $this->_include_js;
			$str = '<?php // '.serialize(array($this->_include,$this->_include_js))."\n".'return function(){extract(func_get_arg(0),EXTR_PREFIX_SAME,"D");?>'.$str.'<?php };?>';
			$this->F($cache,$str);
		}

		$tpls = '';
		$data = array();
		foreach($_d[1] as $k => &$v){
			$tpls .= '<script src="'.__ROOT__.'/public/html/'.$k.'.js?v='.$this->dec62($v['time']).'"></script>';
			$route = explode('/',$k);
			// 组装
			$file = _APP_.'/'.$route[0].'/'.C('xaoi.sys.name.controller').'/'.$route[1].'.php';
			$class = '\\'.$route[0].'\\'.C('xaoi.sys.name.controller').'\\'.$route[1];
			if(is_file($file) && class_exists($class)){
				if(!isset($o[$class]))$o[$class] = new $class();
				if(method_exists($o[$class],$route[2])){
					$method = new ReflectionMethod($class,$route[2]);
					if(!$method->isPublic())$method->setAccessible(true);
					$re = $method->invokeArgs($o[$class],$v['params']);
					if(isset($re[0]) && is_array($re[0])){
						if(!isset($data[$route[0]]))$data[$route[0]] = array();
						if(!isset($data[$route[0]][$route[1]]))$data[$route[0]][$route[1]] = array();

						$data[$route[0]][$route[1]][$route[2]] = $re[0];
					}
					
					if(isset($re[1])){
						if(is_string($re[1])){
							$tpls .= '<script src="'.$re[1].'"></script>';
						}else if(is_array($re[1])){
							foreach($re[1] as &$val){
								if(is_string($val))$tpls .= '<script src="'.$val.'"></script>';
							}
						}
					}
				}
			}
		}
		$tpls = '<script>var GLOBAL={tpl:{},data:'.json($data).'}</script>'.$tpls;

		$tpl = include($cache);
		if(is_callable($tpl)){
			echo call_user_func($tpl,$this->assign,$tpls);
		}else{
			_E('Template parsing error');
		}
		exit;
	}

	private function checkCache($d){
		foreach($d[0] as $k => &$v)if(!is_file($k)||filemtime($k)>$v)return false;
		foreach($d[1] as $k => &$v)if(!is_file('public/html/'.$k.'.js'))return false;
		return true;
	}

	private function compile($path){
		if(is_file($path) && !array_key_exists($path,$this->_include_path)){
			$this->_include_path[$path] = filemtime($path);
			if(!isset($this->_include[$path]))$this->_include[$path] = $this->_include_path[$path];
			$str = file_get_contents($path);
			$d = $this->str_cutting($str,'<\?','\?>');
			$r = '';
			foreach($d as &$v){
				$r.=$v[0].$this->code($v[1]);
			}
			return $r;
		}
	}

	private function compile_js($p){
		if(is_file($p['path']) && !array_key_exists($p['path'],$this->_include_path)){
			$path_time = filemtime($p['path']);
			$this->_include_path[$p['path']] = $path_time;
			if(!isset($this->_include[$p['path']]))$this->_include[$p['path']] = $this->_include_path[$p['path']];
			if(!isset($this->_include_js[$p['str']]))$this->_include_js[$p['str']] = array('time'=>$path_time,'params'=>$p['params']);
			$str = file_get_contents($p['path']);
			$d = $this->str_cutting($str,'<\?','\?>');
			$f = 'typeof GLOBAL.tpl["'.$p['route']['m'].'"] !== "object"&&(GLOBAL.tpl["'.$p['route']['m'].'"]={});
			typeof GLOBAL.tpl["'.$p['route']['m'].'"]["'.$p['route']['c'].'"] !== "object"&&(GLOBAL.tpl["'.$p['route']['m'].'"]["'.$p['route']['c'].'"]={});
			GLOBAL.tpl["'.$p['route']['m'].'"]["'.$p['route']['c'].'"]["'.$p['route']['a'].'"]=function(P,G,M,C,A){';
			foreach($d as &$v){
				$f.='P(\''.$this->set_str_js($v[0]).'\');';
				if(!empty($v[1]))$f .= $this->code_js($v[1]);
			}
			if(!is_file($p['js']) || $path_time > filemtime($p['js'])){
				$this->F($p['js'],$f.'}');
			}
			return 'GLOBAL.include("'.$p['str'].'");';
		}
	}

	private function code_js($op){
		if(strtolower(substr($op,0,3)) === 'js ')return substr($op,3);
		$op = trim($op);
		$i = substr($op,0,1);
		// 输出变量
		if($i === '$')return 'P('.$this->set_var_js($op).');';
		// 输出常量
		else if($i === '#'){
			if(strtolower(substr($op,0,5)) === '#var '){
				$param = array_map('trim', explode('=', substr($op,5),2));				
				if(!empty($param[0]) && !empty($param[1])){
					$this->var[$param[0]] = $param[1];
				}
			}else{
				foreach(explode('.',substr($op,1)) as $vv){
					if(array_key_exists($vv,$this->var)){
						return 'P(\''.$this->set_str_js($this->var[$vv]).'\');';
					}					
				}
			}
		}
		// 执行字符串
		else if($i === '='){
			$fn = substr($op,1);
			if(!empty($fn)){
				return 'P(\''.$this->set_str_js(eval('return '.substr($op,1).';')).'\');';
			}else return;
		}
		// 函数
		else if($i === ':'){
			$fn = substr($op,1);
			if(!empty($fn)){
				$fn = explode(' ',$fn,2);
				$params = array();
				if(!empty($fn[1]))$params = array_values(array_filter(explode(' ',trim($fn[1]))));
				return 'P(\''.$this->set_str_js(call_user_func_array($fn[0],$params)).'\');';
			}else return;
		}else{
			$pos = strpos($op,' ');
			if($pos){
				$a = strtolower(substr($op,0,$pos));
				$b = trim(substr($op,$pos));
			}else{
				$a = strtolower($op);
				$b = '';
			}
			switch($a){
				// if操作
				case 'if':			return 'if('.$this->set_var_js($b).'){';
				case 'else':		return '}else{';
				case 'elseif':		return '}else if('.$this->set_var_js($b).'){';
				case '/if':			return '}';
				// for操作
				case 'for':			return 'for('.$this->set_var_js($b).'){';
				case '/for':		return '}';
				// 引入模版
				case 'tpl':
					if(!empty($b)){						
						$fn = explode(' ',$b,2);
						$b = $this->get_route($fn[0]);
						$b['params'] = empty($fn[1])?array():array_values(array_filter(explode(' ',trim($fn[1]))));
						$b['path'] = _APP_.'/'.$b['route']['m'].'/view/'.$b['route']['c'].'/'.$b['route']['a'].'.html';
						$b['js'] = 'public/html/'.$b['str'].'.js';
						$re = $this->compile_js($b);
						unset($this->_include_path[$b['path']]);
						return $re;
					}else return;
				default:
					return $this->set_var_js($op);
			}
		}

	}

	private function code($op){
		if(!empty($op)){
			if(strtolower(substr($op,0,4)) === 'php ')return '<?php '.substr($op,4).' ?>';
			$op = trim($op);
			$i = substr($op,0,1);
			// 输出变量
			if($i === '$')return '<?php if(isset('.$this->set_var($op).'))echo '.$this->set_var($op).';?>';
			// 输出常量
			else if($i === '#'){
				if(strtolower(substr($op,0,5)) === '#var '){
					$param = array_map('trim', explode('=', substr($op,5),2));				
					if(!empty($param[0]) && !empty($param[1])){
						$this->var[$param[0]] = $param[1];
					}
				}else{
					foreach(explode('.',substr($op,1)) as $vv){
						if(array_key_exists($vv,$this->var)){
							return $this->var[$vv];
						}					
					}
				}
			}
			// 执行字符串
			else if($i === '='){
				$fn = substr($op,1);
				if(!empty($fn)){
					return eval('return '.substr($op,1).';');
				}else return;
			}
			// 函数
			else if($i === ':'){
				$fn = substr($op,1);
				if(!empty($fn)){
					$fn = explode(' ',$fn,2);
					$params = array();
					if(!empty($fn[1]))$params = array_values(array_filter(explode(' ',trim($fn[1]))));
					return call_user_func_array($fn[0],$params);
				}else return;
			}else{
				$pos = strpos($op,' ');
				if($pos){
					$a = strtolower(substr($op,0,$pos));
					$b = trim(substr($op,$pos));
				}else{
					$a = strtolower($op);
					$b = '';
				}
				$r = '';
				switch($a){
					// if操作
					case 'if':			$r = 'if('.$b.'){';			break;
					case 'else':		$r = '}else{';				break;
					case 'elseif':		$r = '}elseif('.$b.'){';	break;
					case '/if':			$r = '}';					break;
					// foreach操作
					case 'foreach':		$r = 'foreach('.$b.'){';	break;
					case '/foreach':	$r = '}';					break;
					// for操作
					case 'for':			$r = 'for('.$b.'){';		break;
					case '/for':		$r = '}';					break;
					// 引入模版
					case 'include':
						end($this->_include_path);
						$path = dirname(key($this->_include_path)).'/'.$b.'.html';
						$re = $this->compile($path);
						unset($this->_include_path[$path]);
						return $re;
					break;
					// 引入模版
					case 'tpl':
						if(!empty($b)){						
							$fn = explode(' ',$b,2);
							$b = $this->get_route($fn[0]);
							$b['params'] = empty($fn[1])?array():array_values(array_filter(explode(' ',trim($fn[1]))));
							$b['path'] = _APP_.'/'.$b['route']['m'].'/view/'.$b['route']['c'].'/'.$b['route']['a'].'.html';
							$b['js'] = 'public/html/'.$b['str'].'.js';
							$s = count($this->_include_js) === 0?'<?php echo func_get_arg(1);?><script src="'.__ROOT__.'/public/static/lib/tpl.js"></script>':'';
							$re = $this->compile_js($b);
							unset($this->_include_path[$b['path']]);
							return $s.'<script>'.$re.'</script>';
						}else return;
					break;
					default:
						$r = 'echo '.$op.';';
					break;
				}
				return '<?php '.$r.' ?>';
			}
		}
	}

	private function get_route($p){
		$route = array('m'=>MODULE,'c'=>CONTROLLER,'a'=>ACTION);
		$p = explode('/',$p,3);
		krsort($p);
		$p = array_values($p);
		if(!empty($p[0]))$route['a'] = $p[0];
		if(!empty($p[1]))$route['c'] = $p[1];
		if(!empty($p[2]))$route['m'] = $p[2];
		return array('route'=>$route,'str'=>implode('/',$route));
	}

	private function set_var($name){
		return preg_replace('/ *\. *([A-Za-z_][A-Za-z0-9_]*) */','[\'\1\']',preg_replace('/\[ *([A-Za-z0-9_]+) *\]/','[\'\1\']',$name));
	}

	private function set_var_js($name){
		return preg_replace('/\$([A-Za-z_][A-Za-z0-9_]*( *\[(\'|")?[A-Za-z0-9_]+\3\] *| *\. *[A-Za-z0-9_]+)*)/','function(){try{return \1||\'\'}catch(e){return \'\';}}()',$name);
	}

	private function set_str_js($s){
		return str_replace(array('\\','\'',"\r\n","\r","\n"),array('\\\\','\\\'','\\r\\n','\\r','\\n'),$s);
	}

	private function str_cutting($str,$l,$r){
		$ma = '/'.$l.'((?:(?!'.$l.').)*?)'.$r.'/';
		preg_match_all($ma,$str,$arr);
		$r = array();
		$cursor = 0;
		foreach($arr[0] as $k => &$v){
			$p = strpos($str,$v,$cursor);
			$a = substr($str,$cursor,$p - $cursor);
			$cursor = $p + strlen($v);
			$r[] = array($a,$arr[1][$k]);
		}
		$r[] = array(substr($str,$cursor,strlen($str) - $cursor),'');
		return $r;
	}

	// 10进制转为62进制
	private function dec62($n) {  
	    $base = 62;  
	    $index = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  
	    $ret = '';  
	    for($t = floor(log10($n) / log10($base)); $t >= 0; $t --) {  
		$a = floor($n / pow($base, $t));  
		$ret .= substr($index, $a, 1);  
		$n -= $a * pow($base, $t);  
	    }  
	    return $ret;  
	}

	// 输出内容到文件
	private function F()
	{
		switch(func_num_args()){
			case 1:
				$p = func_get_arg(0);
				if(!empty($p) && (is_file($p) || substr($p,0,4) == 'http'))return file_get_contents($p);
			break;
			case 2:
				$p = func_get_arg(0);		
				$v = func_get_arg(1);
				if(!empty($p) && !empty($v)){
					if(!is_string($v))$v = serialize($v);
					if(!is_dir(dirname($p)))mkdir(dirname($p),0777,true);
					return file_put_contents($p,$v);
				}
			break;
		}
	}
}