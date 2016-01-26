<?php defined("BASEPATH") or exit("No direct script access allowed");

define("CONFIG_DIR", FCPATH.APPPATH.'config/');

/**
 * The class of Action object
 */
class Action {
	public $controller;
	public $method;
	public $group;
	public $logo;
	public $name;
	public $label;
    public $args;
	public $fields;

	public function __construct($label = null, $controller = null, $method = null) {
		$this->label = $label;
		$this->controller = $controller;
		$this->method = $method;
	}

	public function data() {
		$arr = (array) $this;
		unset($arr['fields']);
		return (object) array_merge($arr, (array) $this->fields);
	}

	public function uri() {
		if(isset($this->controller) && isset($this->method))
			return site_url(strtolower($this->controller).'/'.$this->method.(isset($this->args) ? '/'.$this->args : ''));
		return '';
	}

	public function __get($key) {
		if(isset($this->$key))
			return $this->$key;

		if(gettype($this->fields) === 'string') {
			$this->fields = json_decode($this->fields);
		}

		if(isset($this->fields) && isset($this->fields->$key)) {
			return $this->fields->$key;
		}

		return null;
	}
}

function get_class_script_path($class) {
	$rc = new ReflectionClass($class);
	return $rc->getFileName();
}

function get_timestamp() {
	return strftime('%Y%m%d%H%M%S');
}

function humanize($count, $unit) {
	$result = $count.' '.$unit;
	if($count > 1)
		$result .= 's';
	return $result;
}

function rollover($arr) {
	return array_reduce($arr, function($carry, $item){
		for($i = 0; $i < count($item); $i++) {
			if(!isset($carry[$i]))
				$carray[$i] = array();
			$carry[$i] []= $item[$i];
		}
		return $carry;
	} ,array());
}
    

function parse_datetime($datetime, $default = null) {
	if(is_string($datetime)) {
		if($datetime != '')
			return new DateTime($datetime);
	}
	else {
		if(is_object($datetime) && get_class($datetime) == 'DateTime') {
			return $datetime;
		}
	}
	return $default;
}

function tokenize_time($begin, $end = null, $mode = 'day', $step = 1) {
	$i = DateInterval::createFromDateString(humanize($step, $mode));
	$result = array();

	$date = parse_datetime($begin);
	if($date == null) {
		// If we still can't find the begin date, make it 1 month ago
		$date = new DateTime();
		$date->sub(DateInterval::createFromDateString('1 month'));
	}

	$end = parse_datetime($end, new DateTime());

	while($date <= $end) {
		$tmp = clone $date;
		$result []= $tmp;
		$date->add($i);
	}

	if($result[count($result) -1] != $end) {
		$result []= $end;
	}
	return $result;
}

function get_request_url() {
	$CI = &get_instance();
	return implode('/', $CI->uri->rsegment_array());
}

function clear_breadscrum() {
	$CI = &get_instance();
	$CI->load->library('session');
	$CI->session->set_userdata('bread_scrums', array());
}

function get_breadscrums() {
	$CI = &get_instance();
	$CI->load->library('session');
	return $CI->session->userdata('bread_scrums');
}

function set_breadscrum() {
	$CI = &get_instance();
	if($CI->input->is_cli_request()) // Skip the breadscrum for cli request
		return;
	if(isset($CI->action_model) && !$CI->action_model->getCurrentAction()) {
		return;
	}
	$CI->load->library('session');
	$scrums = get_breadscrums();

	if(count($scrums) && get_request_url() == $scrums[count($scrums) - 1])
		return;

	$scrums []= get_request_url();
	if(count($scrums) > get_ci_config('breadscrum_depth', 5)) {
		array_shift($scrums);
	}
	$CI->session->set_userdata('bread_scrums', $scrums);
}

function is_class($obj, $class) {
    return isset($obj) && is_object($obj) && get_class($obj) == $class;
}

function smarty_get_parent_tag($template) {
	if(isset($template->parent)) {
		$parent = $template->parent;
		return $parent->_tag_stack[count($parent->_tag_stack) - 2][0];
	}
	return '';
}

function stacktrace($level = 2) {
	$trace = debug_backtrace();
	for($i = 1; $i < $level; $i++) {
		$t = $trace[$i];
		ci_log('The trace is', $t);
	}
}

function ci_trace($msg = '') {
	$CI = &get_instance();
	$CI->trace($msg);
}
function ci_log() {
	$CI = &get_instance();
	if(method_exists($CI, 'log'))
		call_user_func_array(array($CI, 'log'), func_get_args());
}
function ci_error() {
	$CI = &get_instance();
	call_user_func_array(array($CI, 'error'), func_get_args());
}
function is_regex($str) {
	return preg_match('/^\/.*\//', $str);
}

function dump_s($obj) {
	ob_start();
	var_dump($obj);
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}

function create_tag($tagname = 'div', $attr = array(), $defaults = array(), $text = null) {
	$CI = &get_instance();
	$CI->load->helper('form');
	foreach($attr as $k => $v) { // Support array in the value, especially for class
		if(is_array($v)) {
			$attr[$k] = implode(' ', $v);
		}
	}
	if($text === null) {
		return '<'.$tagname.' '._parse_form_attributes($attr, $defaults). ' />';
	}
	else {
		return '<'.$tagname.' '._parse_form_attributes($attr, $defaults).'>'.$text.'</'.$tagname.'>';
	}
}

function get_controller_meta() {
	$action = new Action();
	$action->controller = get_controller_class();
	$action->method = get_controller_method();
	$action->args = get_controller_args();
	return $action;
}

function get_controller_args() {
	if(isset($GLOBALS['CURRENT_ARGS'])) 
		return $GLOBALS['CURRENT_ARGS'];
	$CI = &get_instance();
	return array_slice($CI->uri->rsegments, 2);
}

function get_controller_class() {
	$mod = &get_current_module();
	if($mod)
		return get_class($mod);
	$CI = &get_instance();
	return get_class($CI);
}

function get_controller_method() {
	if(isset($GLOBALS['CURRENT_METHOD']))
		return $GLOBALS['CURRENT_METHOD'];
	$RTR = $GLOBALS['ROUTER'];
	return $RTR->fetch_method();
}

function guess_lang() {
	$CI = &get_instance();

	// Check for the cookie first
	$lang = $CI->input->cookie('pinet_language');

	if($lang != '') { // If have the cookie, use it
		return $lang;
	}

	// Guess the language from the browser's accept language head
	$langs = array();
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		// break up string into pieces (languages and q factors)
		preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

		if (count($lang_parse[1])) {
			// create a list like "en" => 0.8
			$langs = array_combine($lang_parse[1], $lang_parse[4]);
			
			// set default to 1 for any without q factor
			foreach ($langs as $lang => $val) {
				if ($val === '') $langs[$lang] = 1;
			}

			// sort list based on value	
			arsort($langs, SORT_NUMERIC);
		}
	}

	// look through sorted list and use first one that matches our languages
	foreach ($langs as $lang => $val) {
		if (strpos($lang, 'de') === 0) {
			// show German site
		} else if (strpos($lang, 'en') === 0) {
			// show English site
		} 
	}
	foreach($langs as $key => $value) {
		if($value == 1) {
			// Try to translate the language to CI one
			switch(strtolower($key)) {
			case 'en-us':
				return 'english';
			case 'zh-cn':
				return 'chinese';
			default:
				return get_default_lang();
			}
			return $key;
		}
	}
	return null;
}

function get_translated_lang(){
    switch(get_lang()){
        case 'english':
            return 'en-US';
        case 'chinese':
            return 'zh-CN';
    }
}

function get_lang() {
	$lang = guess_lang();
	if($lang != null)
		return $lang;
	return get_default_lang();
}

function get_default_lang() {
	return get_ci_config('language');
}

function get_smarty_variable($params, $template, $name, $default = null) {
	if(strpos($name, '$') !== false) {
		$src = str_replace('$', '', $name); // Try to remove the $ in the name
		$parent_vars = $template->parent->tpl_vars; // If not found, try to get the variable from parent
		$t = get_default($parent_vars, $src, '');
		if($t != '')
			return $t;
	}
	$t = get_default($params, $name, ''); // Try to get the variable from parameters first
	if($t != '')
		return $t;
	return $default;
}

function if_not_exists_then_create_dir($dir) {
    if(!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

function write_to_file($content, $filename) {
	$fp = fopen($filename, 'w');
	fwrite($fp, $content);
	fclose($fp);
}

function get_image_size($src) {
	$CI = &get_instance();
	$CI->load->helper('image');

	$file = find_img($src);
	if($file == null) {
		trigger_error('The responsive image can\'t be found');
		return '';
	}

	if (extension_loaded('imagick')) {
		$img = new Imagick($file);
		return $img->getImageGeometry(); 
	}
	if (extension_loaded('gd')) {
		$path_parts = pathinfo($file);
		$ext = $path_parts['extension'];
		if($ext == 'jpg' || $ext == 'jpeg')
			$src_img=imagecreatefromjpeg($file);
		else
			$src_img=imagecreatefrompng($file);
		return array(
			'width' => imageSX($src_img), 
			'height' => imageSY($src_img));
	}
	return array(0, 0);
}

function smarty_plugin_get_variable($params, $template, $name, $required = false) {
	$name = get_default($params, $name, '');
	if($name == '' && $required) {
		trigger_error('The '.$name.' parameter must be set!');
		return '';
	}
	return get_smarty_variable($params, $template, $name, $name);
}

function get_ci_config($name, $default = null) {
	$CI = &get_instance();
	$item = $CI->config->item($name);
	if($item)
		return $item;
	return $default;
}

function build_tag($tag, $params, $content) {
	$attr = array();
	foreach($params as $key => $value) {
		$attr[$key] = $value;
	}
	$ret = array();
	$ret []= '<'.$tag.' '._parse_form_attributes($attr, array()).'>';
	$ret []= $content;
	$ret []= '</'.$tag.'>';
	return implode("\n", $ret);
}

function get_default($arr, $key, $default = '') {
	if(is_object($arr))
		return isset($arr->$key)? $arr->$key: $default;
	if(is_array($arr))
		return isset($arr[$key])? $arr[$key]: $default;
	return $default;
}

function copy_new($src, $class = null) {
	return copy_object($src, null, $class);
}

function copy_arr($src, $dest = null) {
	if($src == null)
		return null;

	if($dest == null) {
		$dest = array();
	}

	foreach($src as $key => $value) {
		$dest[$key] = $value;
	}
	return $dest;
}

function copy_object($src, $dest = null, $class = null) {
	if($src == null)
		return null;

	if($dest == null) {
		if($class == null)
			$dest = new stdclass();
		else
			$dest = new $class();
	}

	foreach($src as $key => $value) {
		$k = str_replace('.', '_', $key);
		$dest->$k = $value;
	}
	return $dest;
}

function insert_at($array, $item, $index) {
	if(gettype($item) === 'object') {
		array_splice( $array, $index, 0, array($item) );
	}
	else {
		array_splice( $array, $index, 0, $item );
	}
	return $array;
}

function copyArray2Obj($src, $dest) {
	foreach($src as $key=>$value) {
		$dest->$key = $value;
	}
}


function obj2array ( &$Instance ) {
    $clone = (array) $Instance;
    $rtn = array ();
    $rtn['___SOURCE_KEYS_'] = $clone;

    while ( list ($key, $value) = each ($clone) ) {
        $aux = explode ("\0", $key);
        $newkey = $aux[count($aux)-1];
        $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
    }

    return $rtn;
}

function make_classes() {
	 $arg_list = func_get_args();
	 $ret = array();
	 foreach($arg_list as $c) {
		 if(is_string($c) && $c != '')
			 $ret[] = array($c);
		 else if(is_array($c))
			 $ret []= $c;
	 }
	 return call_user_func_array('combine_and_unique_arrays', $ret);
}

function combine_arrays() {
	 $numargs = func_num_args();
	 if($numargs < 1)
		 return array();

	 $arg_list = func_get_args();

	 if($numargs == 1)
		 return $arg_list[0];

	 $ret = array();

	 foreach($arg_list as $arg) {
		 if(is_array($arg)) {
			 foreach($arg as $e) {
				 $ret []= $e;
			 }
		 }
	 }
	 return $ret;
}

function combine_and_unique_arrays() {
	$arr = call_user_func_array('combine_arrays', func_get_args());
	$arr = array_unique($arr);
	sort($arr);
	return $arr;
}

function combine_karrays() {
	$numargs = func_num_args();
	if($numargs < 1)
		return array();

	$arg_list = func_get_args();

	if($numargs == 1)
		return $arg_list[0];

	$ret = array();

	foreach($arg_list as $arg) {
		if(is_array($arg)) {
			foreach($arg as $k=>$e) {
				$ret [$k]= $e;
			}
		}
	}
	return $ret;
}

function to_yes($bool) {
    if($bool)
        return 'yes';
    else
        return 'no';
}

function get_upload_path($folder) {
    $upload_path = './uploads/'.$folder;
    if_not_exists_then_create_dir($upload_path);
    return $upload_path;
}

function get_upload_config($folder) {
    $config = array();
    $config['upload_path'] = get_upload_path($folder);
    $config['allowed_types'] = 'gif|jpg|png';
    $config['max_size'] = '1000';
    $config['max_width']  = '1024';
    $config['max_height']  = '768';
    return $config;
}

function send_admin_email($to, $subject, $message, $attachments = array(), $mail_type = 'qq'){
    $CI = &get_instance();
    $CI->config->load('email_settings');
    $CI->load->library('email');
    $config = get_ci_config('email_settings');
    if($config){
        $email_config = $config[$mail_type];
        $from = $config['admin_info']['from'];
        $name = $config['admin_info']['name'];
        $CI->email->initialize($email_config);
        $CI->email->from($from, $name);
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);
        foreach($attachments as $attachment){
            $CI->email->attach($attachment);
        }
        $CI->email->send();
    }
}


/**
 * Builds the request string.
 *
 * The files array can be a combination of the following (either data or file):
 *
 * file => "path/to/file", filename=, mime=, data=
 *
 * @param array params		(name => value) (all names and values should be urlencoded)
 * @param array files		(name => filedesc) (not urlencoded)
 * @return array (headers, body)
 */
function encodeBody ( $params, $files )
{
    $headers  	= array();
    $body		= '';
    $boundary	= 'OAuthRequester_'.md5(uniqid('multipart') . microtime());
    $headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;


    // 1. Add the parameters to the post
    if (!empty($params))
    {
        foreach ($params as $name => $value)
        {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: form-data; name="'.$name.'"';
            $body .= "\r\n\r\n";
            $body .= urldecode($value);
            $body .= "\r\n";
        }
    }

    // 2. Add all the files to the post
    if (!empty($files))
    {
        $untitled = 1;

        foreach ($files as $name => $f)
        {
            $data     = false;
            $filename = false;

            if (isset($f['filename']))
            {
                $filename = $f['filename'];
            }

            if (!empty($f['file']))
            {
                $data = @file_get_contents($f['file']);
                if ($data === false)
                {
                    trigger_error(sprintf('Could not read the file "%s" for form-data part', $f['file']));
                }
                if (empty($filename))
                {
                    $filename = basename($f['file']);
                }
            }
            else if (isset($f['data']))
            {
                $data = $f['data'];
            }

            // When there is data, add it as a form-data part, otherwise silently skip the upload
            if ($data !== false)
            {
                if (empty($filename))
                {
                    $filename = sprintf('untitled-%d', $untitled++);
                }
                $mime  = !empty($f['mime']) ? $f['mime'] : 'application/octet-stream';
                $body .= '--'.$boundary."\r\n";
                $body .= 'Content-Disposition: form-data; name="'.rawurlencode($name).'"; filename="'.rawurlencode($filename).'"'."\r\n";
                $body .= 'Content-Type: '.$mime;
                $body .= "\r\n\r\n";
                $body .= $data;
                $body .= "\r\n";
            }

        }
    }
    $body .= '--'.$boundary."--\r\n";

    $headers['Content-Length'] = strlen($body);
    return array($headers, $body);
}

function read_config_file($file) {
	if(file_exists(CONFIG_DIR.$file)) {
		return file_get_contents(CONFIG_DIR.$file);
	}
	return null;
}

function find_rule($rule) {
	return find_path('config/rules/'.$rule);
}

function find_path() {
	foreach(array('pinet/', APPPATH) as $prefix) {
		foreach(func_get_args() as $p) {
			$path = FCPATH.$prefix.$p;
			if(file_exists($path))
				return $path;
		}
	}
	return null;
}

function get_paths() {
	$ret = array();
	foreach(array('pinet/', APPPATH) as $prefix) {
		foreach(func_get_args() as $p) {
			$ret []= FCPATH.$prefix.$p;
		}
	}
	return $ret;
}

function merge_objects() {
	if(func_num_args() <= 0)
		return null;

	if(func_num_args() == 1)
		return func_get_arg(0);

	$args = func_get_args();
	$obj = array_shift($args);
	foreach($args as $o) {
		if(!is_array($o) && !is_object($o))
			continue;
		foreach($o as $k => $v) {
			if(!isset($obj->$k) && isset($v)) {
				$obj->$k = $v;
			}
		}
	}
	return $obj;
}

function forward() {
	if(func_num_args() > 0) {
		$args = func_get_args();
		$method = array_shift($args);
		$CI = &get_instance();
		if(method_exists($CI, $method)) {
			call_user_func_array(array($CI, $method), $args);
			return true;
		}
	}
	return false;
}

function require_widget_smarty($widget, $smarty = null) {
	foreach(array('pinet/', APPPATH) as $path) {
		foreach(array('block.', 'function.') as $prefix) {
			$p = FCPATH.$path.'widgets/'.$widget.'/smarty/'.$prefix.($smarty == null? $widget: $smarty).'.php';
			if(file_exists($p)) {
				require_once($p);
				return $p;
			}
		}
	}
	return false;
}

function display_error($img) {
	show_error(implode(' ', $img));
	exit;
}

function clips_load_rule($rule) {
	if(is_array($rule)) {
		foreach($rule as $r) {
			clips_load_rule($r);
		}
		return true;
	}
	$CI = &get_instance();
	if(isset($CI) && isset($CI->clips)) {
		foreach(array(APPPATH, 'pinet/') as $p) {
			$path = $p.'config/rules/'.$rule;
			if(file_exists($path)) {
				return $CI->clips->load($path);
			}
		}
	}
	return false;
}

function &get_current_module() {
	return Pinet_Module::get_instance();
}

function generate_ad($zone_id, $type='image'){
    switch($type){
        case 'image':
        default:
            return "<script type='text/javascript'><!--//<![CDATA[
                var m3_u = (location.protocol=='https:'?'https://revive.pinet.co/www/delivery/ajs.php':'http://revive.pinet.co/www/delivery/ajs.php');
                var m3_r = Math.floor(Math.random()*99999999999);
                if (!document.MAX_used) document.MAX_used = ',';
                document.write (\"<scr\"+\"ipt type='text/javascript' src='\"+m3_u);
                document.write (\"?zoneid=$zone_id\");
                document.write ('&amp;cb=' + m3_r);
                if (document.MAX_used != ',') document.write (\"&amp;exclude=\" + document.MAX_used);
                document.write (document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));
                document.write (\"&amp;loc=\" + escape(window.location));
                if (document.referrer) document.write (\"&amp;referer=\" + escape(document.referrer));
                if (document.context) document.write (\"&context=\" + escape(document.context));
                if (document.mmm_fo) document.write (\"&amp;mmm_fo=1\");
                document.write (\"'><\/scr\"+\"ipt>\");
                //]]>--></script><noscript><a href='http://revive.pinet.co/www/delivery/ck.php?n=ac2a362f&amp;cb=INSERT_RANDOM_NUMBER_HERE' target='_blank'><img src='http://revive.pinet.co/www/delivery/avw.php?zoneid=5&amp;cb=INSERT_RANDOM_NUMBER_HERE&amp;n=ac2a362f' border='0' alt='' /></a></noscript>";
            break;
    }
}

/**
 * url 为服务的url地址
 * query 为请求串
 */
function sock_post($url,$query){
    $data = "";
    $info=parse_url($url);
    $fp=fsockopen($info["host"],80,$errno,$errstr,30);
    if(!$fp){
        return $data;
    }
    $head="POST ".$info['path']." HTTP/1.0\r\n";
    $head.="Host: ".$info['host']."\r\n";
    $head.="Referer: http://".$info['host'].$info['path']."\r\n";
    $head.="Content-type: application/x-www-form-urlencoded\r\n";
    $head.="Content-Length: ".strlen(trim($query))."\r\n";
    $head.="\r\n";
    $head.=trim($query);
    $write=fputs($fp,$head);
    $header = "";
    while ($str = trim(fgets($fp,4096))) {
        $header.=$str;
    }
    while (!feof($fp)) {
        $data .= fgets($fp,4096);
    }
    return $data;
}

/**
 * 模板接口发短信
 * apikey 为云片分配的apikey
 * tpl_id 为模板id
 * tpl_value 为模板值
 * mobile 为接受短信的手机号
 */
function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile){
    $url="http://yunpian.com/v1/sms/tpl_send.json";
    $encoded_tpl_value = urlencode("$tpl_value");
    $post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
    return sock_post($url, $post_string);
}

/**
 * 普通接口发短信
 * apikey 为云片分配的apikey
 * text 为短信内容
 * mobile 为接受短信的手机号
 */
function send_sms($apikey, $text, $mobile){
    $url="http://yunpian.com/v1/sms/send.json";
    $encoded_text = urlencode("$text");
    $post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
    return sock_post($url, $post_string);
}

function sms_send_randcode($code, $mobile, $tpl_id=1){
    $CI = &get_instance();
    $CI->config->load('oauth2');
    $sms_info = $CI->config->item('sms_info');
    $api_key = $sms_info['apikey'];
    $company = $sms_info['company'];
    $tpl_value="#code#=$code&#company#=$company";
    return tpl_send_sms($api_key, $tpl_id, $tpl_value, $mobile);
}

function sms_send_randcode_nocompany($code, $mobile, $tpl_id){
    $CI = &get_instance();
    $CI->config->load('oauth2');
    $sms_info = $CI->config->item('sms_info');
    $api_key = $sms_info['apikey'];
    $tpl_value="#code#=$code";
    return tpl_send_sms($api_key, $tpl_id, $tpl_value, $mobile);
}

function generate_shorturl($url){
    $generated_url="http://api.t.sina.com.cn/short_url/shorten.json?source=1681459862&url_long=";
    $url = trim($url);
    if(substr($url,0,7)!="http://"){
        $url = "http://".$url;
    }
    $generated_url.=urlencode($url);
    $respone = @file_get_contents($generated_url);
    $respone = json_decode($respone, true);
    if(is_array($respone)&&count($respone)){
        return $respone[0]['url_short'];
    }
    return '';
}

function check_port($port) {
    $errno = null;
    $errstr = null;
    $e = error_reporting();
    error_reporting(0);
    $fp = fsockopen('127.0.0.1', $port, $errno, $errstr, 5);
    error_reporting($e);
    if($fp) {
        fclose($fp);
        return false;
    }
    else {
        return true;
    }
}

function get_available_port($start = 1080, $end = 65535) {
    while(true) {
        $p = rand($start, $end);
        if(check_port($p))
            return $p;
    }
}

function filter_crlf($post){
    $post = trim($post);
    $post = strip_tags($post,"");
    $post = preg_replace("/\t/","",$post);
    $post = preg_replace("/\r\n/","",$post);
    $post = preg_replace("/\r/","",$post);
    $post = preg_replace("/\n/","",$post);
    $post = preg_replace("/ /","",$post);
    return preg_replace("/'/","",$post);
}

function widget_select_get_options($options, $form_data, $field, $model = null) {
	$ret = $options;
	$ret[-1] =  '-- Please Select --';
	if(isset($model)) {
		$value_col = 'value';
		if(isset($field->value_col)) {
			$value_col = $field->value_col.' as value';
		}
		$model->select('id', $value_col);
		$query = true;
		if(isset($field->filters) && is_object($field->filters)) {
			foreach($field->filters as $k => $v) {
				if(is_object($v)) { // This is dynamic filter
					$f = $v->field;
//					ci_log('The dynamic field is %s, and value is %s and key is %s', $f, $form_data->$f, $k);
					if(isset($form_data) && isset($form_data->$f)) {
						$model->where($k, $form_data->$f);
					}
					else {
						$query = false;
					}
				}
				else {
					$model->where($k, $v);
				}
			}
		}

		if($query)
			$ret = array_reduce($model->get_all(), function($carry, $item) {$carry[$item['id']] = $item['value'];
				return $carry;}, $ret);
	}
	return $ret;
}

function cache_support($file) {
	$last_modified  = filemtime( $file );

	$modified_since = ( isset( $_SERVER["HTTP_IF_MODIFIED_SINCE"] ) ? strtotime( $_SERVER["HTTP_IF_MODIFIED_SINCE"] ) : false );
	$etagHeader     = ( isset( $_SERVER["HTTP_IF_NONE_MATCH"] ) ? trim( $_SERVER["HTTP_IF_NONE_MATCH"] ) : false );

	// This is the actual output from this file (in your case the xml data)
	$content  = $file;
	// generate the etag from your output
	$etag     = sprintf( '"%s-%s"', $last_modified, md5( $content ) );

	//set last-modified header
	header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $last_modified )." GMT" );
	//set etag-header
	header( "Etag: ".$etag );

	// if last modified date is same as "HTTP_IF_MODIFIED_SINCE", send 304 then exit
	if ( (int)$modified_since === (int)$last_modified && $etag === $etagHeader ) {
		header( "HTTP/1.1 304 Not Modified" );
		return true;
	}
	return false;
}

function check_need_to_show($type=''){
    $CI = &get_instance();
    $CI->load->library('mobile_detect');
    $show = $CI->mobile_detect->match('MicroMessenger') || $CI->mobile_detect->match('Yixin');
    if($type && $show){
        return $CI->mobile_detect->match($type) ? 2 : 0;
    }
    return $show ? 0 : 1;
}

function ci_paths($path) {
	return array(FCPATH.APPPATH.$path, FCPATH.'pinet/'.$path);
}

function get_array_next($array, $value) {
	$find = false;
	foreach ($array as $k => $v) {
		if ($find) {
			return array($k, $v);
		}
		if ($v == $value) {
			$find = true;
		}
	}
}

function redirect_post($url, $values, $title='Redirecting...', $message='Redirecting to ', $customize=false){
    $text = '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>'.$title.'</title>
            </head>
            <body>';
    if(!$customize)
        $message .= $url;
    $text .=$message .'<form action="'.$url.'" method="post">';
    foreach($values as $k=>$v){
        $text .= "<input type='hidden' name='$k' value='".$v."'>";
    }
    $text .=<<<TEXT
            </form>
            </body>
            <script type="text/javascript">
                window.onload = function(){ document.forms[0].submit(); };
            </script>
            </html>
TEXT;
    return $text;
}
