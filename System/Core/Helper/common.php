<?php
/**
 * 常用方法  工具方法 大部分方法来自ebh2项目 有删减
 * @author eker-huang
 * @time 2017年3月18日16:22:40
 * 
 */

/**
 * 执行其他控制器
 *
 * userage: runAction('Classroom/Score/getUserSum',array('uid'=>$this->uid,'crid'=>$this->crid));
 * @param $control
 * @param array $data
 * @return bool
 */
function runAction($control,$data = array()){
    $url = new Url();
    $url->_parseUrl( $control);
    $directory = $url->getDirectory();
    $path = APP_PATH . DIRECTORY_SEPARATOR . 'Controller';
    if(!empty($directory)){
        $path .=  DIRECTORY_SEPARATOR . $directory;
    }
    $controllerClass = ucfirst($url->getControl()).'Controller';
    if(!class_exists($controllerClass)){
        $filePath = $path . DIRECTORY_SEPARATOR . ucfirst($url->getControl()) . '.php';

        if(!file_exists($path . DIRECTORY_SEPARATOR . ucfirst($url->getControl()) . '.php')){
            return false;
        }
        require_once $path . DIRECTORY_SEPARATOR . ucfirst($url->getControl()) . '.php';
    }
    $controller = new $controllerClass;
    $controller->init();
    $action = $url->getMethod().'Action';
    if(!method_exists($controller,$action)){
        return false;
    }

    if(!empty($data)){
        foreach ($data as $key=>$value){
            $controller->$key = $value;
        }
    }

    return $controller->$action();
}

/**
 * 返回给客户端的信息结构
 * status： 1=成功返回  0=失败返回
 * @param int $status
 * @param string $msg
 * @param array $data
 * @return array
 */
function returnData($status = 1,$msg = '',$data = array()){
    return array(
        'status'  =>  $status,
        'data'  =>  $data,
        'msg'   =>  $msg
    );
}

/**
 * json格式输出
 * @param number $code 状态标识 0 成功 1 失败
 * @param string $msg 输出消息
 * @param array $data 数组参数数组
 * @param string $exit 是否结束退出
 */
function renderjson($code=0,$msg="",$data=array(),$exit=true){
    $arr = array(
        'code'=>(intval($code) ===0) ? 0 : intval($code),
        'msg'=>$msg,
        'data'=>$data
    );
    echo json_encode($arr);
    if($exit){
        exit();
    }
}

/**
 * 日志调试
 * @param unknown $msg
 * @param string $level
 * @param string $php_error
 */
function log_message($msg, $level = 'info') {
	switch ($level){
		case 'error': Ebh()->log->error($msg);
			break;
		case 'debug': Ebh()->log->debug($msg);
			break;
		case 'info': Ebh()->log->info($msg);
			break;
		default: Ebh()->log->info($msg);	
	}
}

/**
 * 获取url
 * @param unknown $name
 * @param string $echo
 * @return string|unknown
 */ 
function geturl($name, $echo = FALSE) {
    if (strpos($name, 'http://') !== FALSE || strpos($name, '.html') !== FALSE) {
        $url = $name;
    } else
        $url = '/' . $name . '.html';
    if ($echo)
        echo $url;
    return $url;
}

/**
 * 获取开发平台登录url
 * 统一到www.ebh.net授权
 * @param unknown $type
 * @param string $returnurl
 */
function getopenloginurl($type,$returnurl='/'){
	$baseurl = "http://www.ebh.net";
	$url = '';
	switch ($type){
		case 'qq':$url=geturl('otherlogin/qq');
			break;
		case 'sina':$url=geturl('otherlogin/sina');
			break;
		case 'wx':$url=geturl('otherlogin/wx');
			break;
	}
	return $baseurl.$url."?returnurl=".urlencode($returnurl);
}

/**
 * 获取当前域名
 */
function getdomain($url=""){
	$domain = '/';
	if(!empty($url)){
		if(preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is',$url)==true){
			$arr_url = parse_url($url);
			$domain =  "http://".$arr_url['host'];
		}
	}else{
		$domain = "http://".$_SERVER['HTTP_HOST'];
	}
	return $domain;
}


/**
 * 加密/解密函数
 * @param unknown $string 要处理的字符串 明文 或 密文
 * @param string $operation 默认加密  DECODE时为解密
 * @param string $key  密匙  
 * @param number $expiry 密文有效期
 * @return string
 */
function authcode($string, $operation='ENCODE', $key = '', $expiry = 0) {
    $authkey = Ebh()->config->get('system.security.authkey');
    $ckey_length = 4; // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : $authkey);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 根据原始图片文件,获取缩略图路径
 * 例子：getthumb('http://www.ebanhui.com/images_avater/2014/01/23/1390475735.jpg','120_120');
 * 则返回 http://www.ebanhui.com/images_avater/2014/01/23/1390475735_120_120.jp
 * @param string $imageurl	原始图片的路径
 * @param string $size	获取的规格大小  用"_"分隔开
 * @param string $defaulturl Description
 */
function getthumb($imageurl, $size, $defaulturl = '') {
	if(empty($imageurl))
		return $defaulturl;
    $ipos = strrpos($imageurl, '.');
    if ($ipos === FALSE)
        return $imageurl;
    $newimagepath = substr($imageurl, 0, $ipos) . '_' . $size . substr($imageurl, $ipos);
    return $newimagepath;
}

//生成随机字符串或数字
function random($length, $numeric = 0) {
    PHP_VERSION < '4.2.0' ? mt_srand((double) microtime() * 1000000) : mt_srand();
    $seed = base_convert(md5(print_r($_SERVER, 1) . microtime()), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}








/**
 * XSS安全过滤 来自thinkphp
 * @param unknown $val
 * @return mixed
 */
function remove_xss($val) {
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
   // this prevents some character re-spacing such as <java\0script>
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
   // $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

   // straight replacements, the user should never need these since they're normal characters
   // this prevents like <IMG SRC=@avascript:alert('XSS')>
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      // ;? matches the ;, which is optional
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

      // @ @ search for the hex values
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
      // @ @ 0{0,7} matches '0' zero to seven times
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
   }

   // now the only remaining whitespace attacks are \t, \n, and \r
   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);

   $found = true; // keep replacing as long as the previous round replaced something
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(&#0{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
         if ($val_before == $val) {
            // no replacements were made, so exit the loop
            $found = false;
         }
      }
   }
   return $val;
}
/**
 * 获取安全html
 * @param unknown $text
 * @param unknown $tags 要保留的标签 | 分割
 * @return mixed|unknown
 */
function h($text, $tags = '') {
    $text   =   trim($text);
    //完全过滤注释
    $text   =   preg_replace('/<!--?.*-->/','',$text);
    //完全过滤动态代码
    $text   =   preg_replace('/<\?|\?'.'>/','',$text);
    //完全过滤js
    $text   =   preg_replace('/<script?.*\/script>/','',$text);

    $text   =   str_replace('[','&#091;',$text);
    $text   =   str_replace(']','&#093;',$text);
    $text   =   str_replace('|','&#124;',$text);
    //过滤换行符
    $text   =   preg_replace('/\r?\n/','',$text);
    //br
    $text   =   preg_replace('/<br(\s*\/)?'.'>/i','[br]',$text);
    $text   =   preg_replace('/<p(\s*\/)?'.'>/i','[p]',$text);
    $text   =   preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
	$text   =   str_replace('font','{f{o{n{t{',$text);
	$text   =   str_replace('decoration','{d{e{c{o{r{a{t{i{o{n{',$text);
	$text   =   str_replace('<strong>','{s{t{r{o{n{g{',$text);
	$text   =   str_replace('</strong>','}s{t{r{o{n{g{',$text);
	$text   =   str_replace('background-color','{b{a{c{k{g{r{o{u{n{d{-{c{o{l{o{r',$text);
	

    //过滤危险的属性，如：过滤on事件lang js
    while(preg_match('/(<[^><]+)(on(?=[a-zA-Z])|lang|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
        $text=str_replace($mat[0],$mat[1],$text);
    }
    while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
        $text=str_replace($mat[0],$mat[1].$mat[3],$text);
    }
    if(empty($tags)) {
        $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a|span|input|h1|h2|h3|h4|h5';
    }
    //允许的HTML标签
    $text = preg_replace('/<('.$tags.')( [^><\[\]]*)?>/i','[\1\2]',$text);
    $text = preg_replace('/<\/('.$tags.')>/Ui','[/\1]',$text);
    //过滤多余html
    $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml|pre)[^><]*>/i','',$text);
    //过滤合法的html标签
    while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
        $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
    }
    //转换引号
    while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
        $text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
    }
    //过滤错误的单个引号
    while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
        $text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
    }
    //转换其它所有不合法的 < >
    $text   =   str_replace('<','&lt;',$text);
    $text   =   str_replace('>','&gt;',$text);
    $text   =   str_replace('"','&quot;',$text);
     //反转换
    $text   =   str_replace('[','<',$text);
    $text   =   str_replace(']','>',$text);
    $text   =   str_replace('&#091;','[',$text);
    $text   =   str_replace('&#093;',']',$text);
    $text   =   str_replace('|','"',$text);
    //过滤多余空格
    $text   =   str_replace('  ',' ',$text);
	$text   =   str_replace('{f{o{n{t{','font',$text);
	$text   =   str_replace('{s{t{r{o{n{g{','<strong>',$text);
	$text   =   str_replace('}s{t{r{o{n{g{','</strong>',$text);
	$text   =   str_replace('{d{e{c{o{r{a{t{i{o{n{','decoration',$text);
	$text   =   str_replace('{b{a{c{k{g{r{o{u{n{d{-{c{o{l{o{r','background-color',$text);
    //剔除class标签属性
    $text = preg_replace_callback('/<.*?(class\=([\'|\"])(.*?)(\2)).*?>/is', function($grp){
        return str_ireplace($grp[1], '', $grp[0]);
    }, $text);
    //抹去所有外链接
    $text = replace_Links($text);
    return $text;
}


/**
 * 数据安全过滤
 * @param unknown $datas
 * @return unknown|mixed|unknown
 */
function safefilter($datas){
	if(empty($datas)){
		return $datas;
	}
	if(is_array($datas)){
		foreach ($datas as &$data) {
			$data = safefilter($data);
		}
	}else{
		$datas = h($datas);
	}
	return $datas;
}

/**
 * 从一段文本中去除别的网站的a链接
 * @param unknown $body
 * @param array $allow_urls
 * @return mixed
 */
function replace_Links(&$body, $allow_urls=array()){
	if(empty($allow_urls)){
		$allow_urls = array(
				'ebh.net',
				'ebanhui.com',
				'svnlan.com'
			);
	}
	$host_rule = join('|', $allow_urls);
	$host_rule = preg_replace("#[\n\r]#", '', $host_rule);
	$host_rule = str_replace('.', "\\.", $host_rule);
	$host_rule = str_replace('/', "\\/", $host_rule);
	$arr = '';
	preg_match_all("#<a([^>]*)>(.*)<\/a>#iU", $body, $arr);
	if( is_array($arr[0]) ){
		$rparr = array();
		$tgarr = array();
		foreach($arr[0] as $i=>$v){
			if( $host_rule != '' && preg_match('#'.$host_rule.'#i', $arr[1][$i]) ){
				continue;
			} else {
				$rparr[] = $v;
				$tgarr[] = $arr[2][$i];
			}
		}if( !empty($rparr) ){
			$body = str_replace($rparr, $tgarr, $body);
		}
	}
	$arr = $rparr = $tgarr = '';
	return $body;
}

/**
 * 获取IP
 * 该方法获取的是web服务器自己的ip
 * @return string|unknown
 */
function getip(){
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	}else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}else if(!empty($_SERVER["REMOTE_ADDR"])){
		$cip = $_SERVER["REMOTE_ADDR"];
	}else{
		$cip = "127.0.0.1";
	}
	return $cip;
}

/**
 * 获取终端浏览器ip
 */
function getclientip(){
    $ip = '127.0.0.1';
    if(!empty($_POST['client_ip'])){
        $ip = $_POST['client_ip'];
    }
    return $ip;
}

 
/**
 * 将秒数转化为天/小时/分/秒
 * @param unknown $time
 * @return string
 */
function secondToStr($time){
	$str = '';
	$timearr = array(86400 => '天', 3600 => '小时', 60 => '分', 1 => '秒');
	foreach ($timearr as $key => $value) {
		if ($time >= $key)
			$str .= floor($time/$key) . $value;
		$time %= $key;
	}
	return $str;
}

/**
 * 将秒数转为/小时/分/秒
 * @param unknown $time
 * @return string
 */
function secondToHstr($time){
	$str = '';
	$timearr = array(3600 => '小时', 60 => '分', 1 => '秒');
	foreach ($timearr as $key => $value) {
		if ($time >= $key)
			$str .= floor($time/$key) . $value;
		$time %= $key;
	}
	return $str;
}

/**
 *获取header头信息,兼容nginx 
 */
if (!function_exists('getallheaders')){  
    function getallheaders(){
       $headers = array();
       foreach ($_SERVER as $name => $value)   
       {  
           if (substr($name, 0, 5) == 'HTTP_')   
           {  
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;  
           }  
       }  
       return $headers;  
    } 
}


function do_post($url, $data , $retJson = true ,$setHeader = false){
    //$auth = Ebh::app()->getInput()->cookie('auth');
   // $uri = Ebh::app()->getUri();
   // $domain = $uri->uri_domain();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if ($setHeader) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );
    }
    //curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_POST, TRUE); 
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_URL, $url);
   // curl_setopt($ch, CURLOPT_COOKIE, 'ebh_auth='.urlencode($auth).';ebh_domain='.$domain);
    $ret = curl_exec($ch);
    curl_close($ch);
    if($retJson == false){
        $ret = json_decode($ret);
    }
    return $ret;
}

/**
 * 创建一个 CURLFile 对象, 用与上传文件
 * filename 上传文件的路径
 * mimetype 文件的Mimetype
 * postname 文件名
 * 
 */
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename="
            . ($postname ?: basename($filename))
            . ($mimetype ? ";type=$mimetype" : '');
    }
}

/**
 * 时间戳转成易于理解的格式
 * @param  int $timestamp 时间
 * @return string  返回格式：刚刚、几分钟前、几个小时前、昨天、前天、几天前，超过一个月的显示完整时间。
 */
function timetostr($timestamp,$format = 'Y-m-d H:i'){
	$today_time = strtotime('today');
	$timediff_today = $today_time - $timestamp;//timestamp和今天0点时间差
	$timediff_now = SYSTIME - $timestamp;//timestamp和当前时间差
	if ($timediff_now < 0){
		return;
	}
	if ($timediff_today <= 0){
		if ($timediff_now < 60){
			return '刚刚';
		}elseif ($timediff_now >= 60 && $timediff_now < 3600){
			return floor($timediff_now/60) . '分钟前';
		}elseif ($timediff_now >= 3600 && $timediff_now < 86400){
			return floor($timediff_now/3600) . '小时前';
		}
	}else{
		if ($timediff_today < 86400){
			return '昨天';
		}elseif ($timediff_today >= 86400 && $timediff_today < 172800){
			return '前天';
		}elseif ($timediff_today >= 172800 && $timediff_today <= 259200){
			return ceil($timediff_today/86400) . '天前';
		}else{
			return date($format, $timestamp);
		}
	}
}
/**
 * 获取用户头像
 * @param unknown $user
 * @param string $size
 */
function getavater($user,$size='120_120'){
	$defaulturl = "http://static.ebanhui.com/ebh/tpl/default/images/";
	$face = "";
	if(!empty($user['face'])){
		$ext = substr($user['face'], strrpos($user['face'], '.'));
		$face = str_replace($ext,'_'.$size.$ext,$user['face']);
	}else{
		if(isset($user['sex'])){
			if($user['sex']==1){//女
				$face = (!empty($user['groupid']) && $user['groupid'] == 5) ? $defaulturl."t_woman.jpg" : $defaulturl."m_woman.jpg";
				$face = str_replace('.jpg','_'.$size.'.jpg',$face);
			}else{//男
				$face = (!empty($user['groupid']) && $user['groupid'] == 5) ? $defaulturl."t_man.jpg" : $defaulturl.'m_man.jpg';
				$face = str_replace('.jpg','_'.$size.'.jpg',$face);
			}
		}else{
			$face = $defaulturl.'m_man.jpg';
			$face = str_replace('.jpg','_'.$size.'.jpg',$face);
		}
	}
	return $face;
}

/**
 * 获取用户名
 * @author eker
 * 1.realname存在 优先返回
 * 2.否则 username
 * 3.附带截取功能 中文截取字数
 */
function  getusername($user,$len=0){
	$name = '';
	if(!empty($user['realname'])){
		$name = $user['realname'];
	}elseif(!empty($user['username']) && empty($user['realname']) ){
		$name = $user['username'];
	}
	if($len>0){
		$name = shortstr($name,$len,'...');
	}
	
	return $name;
}
 
/**
 * 按照给定长度截取字符串
 * @param string $str源字符串
 * @param int $length 需要截取的长度
 * @param string $pre，字符串附加的字符，默认为...
 * @return string 返回截取后的字符串
 */
function shortstr($str, $length = 20, $pre = '...') {
	$resultstr = ssubstrch($str, 0, $length);
	return strlen($resultstr) == strlen($str) ? $resultstr : $resultstr . $pre;
}

/**
 * 切割中文字符串， 中文占2个字节，字母占一个字节
 * @param $string 要切割的字符串
 * @param $start 起始位置
 * @param $length 切割长度
 */
function ssubstrch($string, $start = 0, $length = -1) {
	$p = 0;
	$co = 0;
	$c = '';
	$retstr = '';
	$startlen = 0;
	$len = strlen($string);
	$charset = 'UTF-8';
	for ($i = 0; $i < $len; $i ++) {
		if ($length <= 0) {
			break;
		}
		$c = ord($string {$i});
		if ($charset == 'UTF-8') {
			if ($c > 252) {
				$p = 5;
			} elseif ($c > 248) {
				$p = 4;
			} elseif ($c > 240) {
				$p = 3;
			} elseif ($c > 224) {
				$p = 2;
			} elseif ($c > 192) {
				$p = 1;
			} else {
				$p = 0;
			}
		} else {
			if ($c > 127) {
				$p = 1;
			} else {
				$p = 0;
			}
		}
		if ($startlen >= $start) {
			for ($j = 0; $j < $p + 1; $j ++) {
				$retstr .= $string [$i + $j];
			}
			$length -= ($p == 0 ? 1 : 2);
		}
		$i += $p;
		$startlen++;
	}
	return $retstr;
}

/**
 * php版本低于php5.5array_column
 * 返回输入数组中某个单一列的值。
 * $arr 必需 指定要使用的多维数组
 * $column_name 必需,需要返回值的列
 */
if(function_exists('array_column') === false) {
    function array_column($arr, $column_name) {
        $tmp = array();
        foreach($arr as $item) {
            $tmp[] = $item[$column_name];
        }
        return $tmp;
    }
}

/**
 * 以某个数组值当作主键生成数组
 * @param  [array] $data [原数组]
 * @param  string $key  [数组键值]
 * @return [array]       [生成新数组]
 */
if(function_exists('array_coltokey')===false){
    function array_coltokey($arr, $key = 'id') {
        return array_combine(array_column($arr, $key), $arr);
    }
}




/**
*判断当前的访问设备为 安卓pad app
*/
function isApp() {
	if (isset($_SERVER['HTTP_ISEBH']) && $_SERVER['HTTP_ISEBH'] == '1') {
		return TRUE;
	}
	return FALSE;
}

/**
 * 调试输出简写
 * @param unknown $param
 */
function p($param){
	if(is_string($param)){
		echo $param;
	}else{
		echo '<pre>';
		print_r($param);
	}
}

if(!function_exists('getPage')){
    /**
     * @describe:获取分页参数
     * @User:tzq
     * @Date:${DATE}
     * @param $total      int 总页数
     * @param $listRows   int 每页数量
     * @param $curr       int 当前页
     * @return array          参数数组
     */
    function getPage($total,$listRows,$curr){
        $pageCount = ceil($total/$listRows);
        $curr = $curr > 1?$curr:1;
        if($curr == 1){
            $isFirst = true;
        }else{
            $isFirst = false;
        }
        if($curr >= $pageCount){
            $isEnd   = true;
        }else{
            $isEnd   = false;
        }

        return array('total'=>$total,'curr'=>$curr,'listRows'=>$listRows,'pageCount'=>$pageCount,'isFirst'=>$isFirst,'isEnd'=>$isEnd);
    }
}
if (!function_exists('getTimeToString')) {
    /**
     * @describe:将秒转为时分秒
     * @User:tzq
     * @Date:2017/11/18
     * @param $min
     * @return string
     */
    function getTimeToString($min)
    {

        $time = '';
        if ($min >= 3600) {
            $H    = intval($min / 3600);
            $time .= $H . '小时';
            $min  = $min % 3600;
        }
        if ($min >= 60) {
            $I    = intval($min / 60);
            $time .= $I . '分';
            $min  = $min % 60;
        }
        if ($min > 0) {
            $S    = $min;
            $time .= $S . '秒';
        }
        return $time;

    }

}

if(!function_exists('implodeKey')){
    /**
     * @describe:生成缓存key值
     * @User:tzq
     * @Date:2017/11/18
     * @param $params  string 数组或字符串
     * @return string
     */
    function implodeKey($params){
            $buttr = '';
        if(is_array($params)){
            sort($params);
            foreach ($params as $k=>$v){
                $buttr .= $k.'='.$v.'&';
            }
            $buttr = trim($buttr,'&');

        }else{
            $buttr = $params;
        }
        return md5($buttr);
    }
}

if(!function_exists('arraySequence')) {

    /**
     * @describe:二维数组根据字段进行排序
     * @User:tzq
     * @Date:2017/11/30
     * @params array $array 需要排序的数组
     * @params string $field 排序的字段
     * @params string $sort 排序顺序标志 4 降序；3 升序
     */
    function arraySequence($array, $field, $sort = SORT_DESC)
    {
        $column = array_column($array,$field);

        if(!empty($column)){
        array_multisort($column, constant($sort), $array);
        }
        return $array;
    }


}