<?php defined("BASEPATH") or exit("No direct script access allowed");

// ======================================================================
//
// This is the helpers for testing, not for production use
//
// @autor Jack
// @date Jul 17, 2014
//
// ======================================================================

// ======================================================================
//
// The fake dummies
//
// ======================================================================

class FakeName {

	public $first_name;
	public $last_name;
	public $simple_name;
	public $name;

	function __construct($name) {
		$array = explode(' ', $name);
		$this->name = $name;
		if(isset($array[0])) {
			$this->first_name = $array[0];
		}
		if(isset($array[1])) {
			$this->last_name = $array[0];
		}
		$this->simple_name = strtolower(str_replace(' ', '_', $name));
	}
}

function choice($array) {
	return $array[array_rand($array)];
}

function fake_domain() {
	static $fake_domains = array('163.com', '51.la', 'about.com', 'addthis.com', 'adobe.com', 'amazon.co.uk', 'amazon.com', 'ameblo.jp', 'aol.com', 'apple.com', 'baidu.com', 'bbc.co.uk', 'bing.com', 'blogger.com', 'blogspot.com', 'clickbank.net', 'cnn.com', 'creativecommons.org', 'dailymotion.com', 'delicious.com', 'digg.com', 'ebay.com', 'etsy.com', 'europa.eu', 'facebook.com', 'fc2.com', 'feedburner.com', 'flickr.com', 'forbes.com', 'free.fr', 'geocities.com', 'gnu.org', 'go.com', 'godaddy.com', 'goo.gl', 'google.co.jp', 'google.co.uk', 'google.com', 'google.de', 'gov.uk', 'guardian.co.uk', 'homestead.com', 'huffingtonpost.com', 'hugedomains.com', 'icio.us', 'imdb.com', 'instagram.com', 'issuu.com', 'jimdo.com', 'joomla.org', 'linkedin.com', 'livejournal.com', 'macromedia.com', 'mail.ru', 'mapquest.com', 'microsoft.com', 'miibeian.gov.cn', 'mozilla.org', 'msn.com', 'myspace.com', 'networkadvertising.org', 'nih.gov', 'nytimes.com', 'photobucket.com', 'pinterest.com', 'qq.com', 'rambler.ru', 'reddit.com', 'reuters.com', 'sina.com.cn', 'slideshare.net', 'sourceforge.net', 'statcounter.com', 'stumbleupon.com', 't.co', 'taobao.com', 'technorati.com', 'theguardian.com', 'tinyurl.com', 'tripod.com', 'tumblr.com', 'twitter.com', 'typepad.com', 'vimeo.com', 'vk.com', 'w3.org', 'washingtonpost.com', 'webs.com', 'weebly.com', 'weibo.com', 'wikipedia.org', 'wix.com', 'wordpress.com', 'wordpress.org', 'wsj.com', 'yahoo.co.jp', 'yahoo.com', 'yandex.ru', 'youtu.be', 'youtube.com');
	return choice($fake_domains);
}

function fake_mac() {
	return implode(':',str_split(substr(md5(mt_rand()),0,12),2));
}

function fake_device($owner_id, $gateway_id) {
    list($browser, $os) = chooseRandomBrowserAndOs();
	return array(
		'owner_id' => $owner_id,
		'gateway_id' => $gateway_id,
		'mac' => fake_mac(),
		'os' => $os,
		'uagent' => fake_uagent()	
	);
}

function fake_name($name = null) {
	if($name != null && $name != '') {
		return new FakeName($name);
	}
	static $fake_names = array('Elten Sweet', 'Drew Clayton', 'Millson Stampes', 'Melvin Goodwin', 'Dwite Harding', 'Norton Atterton', 'Lindon Atherton', 'Brian Mitchell', 'Marsdon Holton', 'Jean Barney', 'Mina Eastoft', 'Kasandra Nottley', 'Christian Prescott', 'Riley Read', 'Corinne Southey', 'Kenzie Knotley', 'Bobby Snape', 'Berthe Smithies', 'Breana Nash', 'Jazmyne Smith', 'Normal Blackwood', 'Rawson Harrington', 'Wingate Benson', 'Forbes Spaulding', 'Raleigh Foy', 'Jack Burlingame', 'Waylon Altham', 'Herbert Royston', 'Quintin Sutton', 'Abraham Harding');

	return new FakeName(choice($fake_names));
}

function fake_ip($start = '192.168.22.1', $end = '192.168.22.200') {
    if (strcmp($start, $end) > 0) {
        return false;
    }

    $arrStart = explode('.',$start);
    $arrEnd = explode('.', $end);

    // First
    $arrIp[0] = rand($arrStart[0], $arrEnd[0]);

    // Second
    if ($arrIp[0] == $arrStart[0] && $arrIp[0] == $arrEnd[0]) {
        $arrIp[1] = rand($arrStart[1], $arrEnd[1]);
    } elseif ($arrIp[0] == $arrStart[0]) {
        $arrIp[1] = rand($arrStart[1], 255);
    } elseif ($arrIp[0] == $arrEnd[0]) {
        $arrIp[1] = rand(0, $arrEnd[1]);
    } else {
        $arrIp[1] = rand(0, 255);
    }

    // Third
    if ($arrIp[1] == $arrStart[1] && $arrIp[1] == $arrEnd[1]) {
        $arrIp[2] = rand($arrStart[2], $arrEnd[2]);
    } elseif ($arrIp[1] == $arrStart[1]) {
        $arrIp[2] = rand($arrStart[2], 255);
    } elseif ($arrIp[1] == $arrEnd[1]) {
        $arrIp[2] = rand(0, $arrEnd[2]);
    } else {
        $arrIp[2] = rand(0, 255);
    }

    // Fourth
    if ($arrIp[2] == $arrStart[2] && $arrIp[02] == $arrEnd[2]) {
        $arrIp[3] = rand($arrStart[3], $arrEnd[3]);
    } elseif ($arrIp[2] == $arrStart[2]) {
        $arrIp[3] = rand($arrStart[3], 255);
    } elseif ($arrIp[2] == $arrEnd[2]) {
        $arrIp[3] = rand(0, $arrEnd[3]);
    } else {
        $arrIp[3] = rand(0, 255);
    }

    return implode(".", $arrIp);
}

function fake_email($name = null, $domain = null) {
	$n = $name? $name: fake_name();
	$domain = $domain? $domain: fake_domain();
	return $n->simple_name."@".$domain;
}

function fake_user($id = 1000, $name = null) {
	if($name == null)
		$fakename = fake_name();
	else
		$fakename = fake_name($name);
	return (object)array(
		'id' => $id,
		'email_address' => fake_email($fakename),
		'username' => $fakename->simple_name,
        'password' => md5('password')
	);
}

function n_times($n, $func, $start = 1) {
	$ret = array();
	for($i = $start; $i < $n + $start; $i++) {
		$ret []= call_user_func($func, $i);
	}
	return $ret;
}

function fake_gateway($owner_id, $name = null, $id = 1000, $serial = null) {
	$CI = &get_instance();
	$CI->load->library('uuid');

	$the_serial = $serial? $serial: $CI->uuid->get();
	$the_name = $name? $name: 'Gateway['.$id.']';
	return (object) array(
		'id' => $id,
		'owner_id' => $owner_id,
		'name'=> $the_name,
		'serial' => $the_serial,
		'mac' => fake_mac(),
		'ip' => ip2long(fake_ip())
	);
}

/**
 * Random user agent creator
 * @since Sep 4, 2011
 * @version 1.0
 * @link http://360percents.com/
 * @author Luka Pušić <pusic93@gmail.com>
 */

/**
 * Sources:
 * http://en.wikipedia.org/wiki/Usage_share_of_web_browsers#Summary_table
 * http://statowl.com/operating_system_market_share_by_os_version.php
 */
function chooseRandomBrowserAndOS() {
    $frequencies = array(
        34 => array(
            89 => array('chrome', 'win'),
            9 => array('chrome', 'mac'),
            2 => array('chrome', 'lin')
        ),

        32 => array(
            100 => array('iexplorer', 'win')
        ),

        25 => array(
            83 => array('firefox', 'win'),
            16 => array('firefox', 'mac'),
            1 => array('firefox', 'lin')
        ),

        7 => array(
            95 => array('safari', 'mac'),
            4 => array('safari', 'win'),
            1 => array('safari', 'lin')
        ),

        2 => array(
            91 => array('opera', 'win'),
            6 => array('opera', 'lin'),
            3 => array('opera', 'mac')
        )
    );

    $rand = rand(1, 100);
    $sum = 0;
    foreach ($frequencies as $freq => $osFreqs) {
        $sum += $freq;
        if ($rand <= $sum) {
            $rand = rand(1, 100);
            $sum = 0;
            foreach ($osFreqs as $freq => $choice) {
                $sum += $freq;
                if ($rand <= $sum) {
                    return $choice;
                }
            }
        }
    }

    throw new Exception("Frequencies don't sum to 100.");
}
    

function array_random(array $array) {
    return $array[array_rand($array, 1)];
}

function nt_version() {
    return rand(5, 6) . '.' . rand(0, 1);
}

function ie_version() {
    return rand(7, 9) . '.0';
}

function trident_version() {
    return rand(3, 5) . '.' . rand(0, 1);
}

function osx_version() {
    return "10_" . rand(5, 7) . '_' . rand(0, 9);
}

function chrome_version() {
    return rand(13, 15) . '.0.' . rand(800, 899) . '.0';
}

function presto_version() {
    return '2.9.' . rand(160, 190);
}

function presto_version2() {
    return rand(10, 12) . '.00';
}

function firefox($arch) {
    $ver = array_random(array(
	    'Gecko/' . date('Ymd', rand(strtotime('2011-1-1'), time())) . ' Firefox/' . rand(5, 7) . '.0',
	    'Gecko/' . date('Ymd', rand(strtotime('2011-1-1'), time())) . ' Firefox/' . rand(5, 7) . '.0.1',
	    'Gecko/' . date('Ymd', rand(strtotime('2010-1-1'), time())) . ' Firefox/3.6.' . rand(1, 20),
        'Gecko/' . date('Ymd', rand(strtotime('2010-1-1'), time())) . ' Firefox/3.8'
    ));

    switch ($arch) {
    case 'lin':
        return "(X11; Linux {proc}; rv:" . rand(5, 7) . ".0) $ver";
    case 'mac':
        $osx = osx_version();
        return "(Macintosh; {proc} Mac OS X $osx rv:" . rand(2, 6) . ".0) $ver";
    case 'win':
    default:
        $nt = nt_version();
        return "(Windows NT $nt; {lang}; rv:1.9." . rand(0, 2) . ".20) $ver";

    }
}

function safari($arch) {
    $saf = rand(531, 535) . '.' . rand(1, 50) . '.' . rand(1, 7);
    if (rand(0, 1) == 0) {
        $ver = rand(4, 5) . '.' . rand(0, 1);
    } else {
        $ver = rand(4, 5) . '.0.' . rand(1, 5);
    }

    switch ($arch) {
    case 'mac':
        $osx = osx_version();
        return "(Macintosh; U; {proc} Mac OS X $osx rv:" . rand(2, 6) . ".0; {lang}) AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf";
    //case 'iphone':
    //    return '(iPod; U; CPU iPhone OS ' . rand(3, 4) . '_' . rand(0, 3) . " like Mac OS X; {lang}) AppleWebKit/$saf (KHTML, like Gecko) Version/" . rand(3, 4) . ".0.5 Mobile/8B" . rand(111, 119) . " Safari/6$saf";
    case 'win':
    default:
        $nt = nt_version();
        return "(Windows; U; Windows NT $nt) AppleWebKit/$saf (KHTML, like Gecko) Version/$ver Safari/$saf";
    }

}

function iexplorer($arch) {
    $ie_extra = array(
        '',
        '; .NET CLR 1.1.' . rand(4320, 4325) . '',
        '; WOW64'
    );

    $nt = nt_version();
    $ie = ie_version();
    $trident = trident_version();
    return "(compatible; MSIE $ie; Windows NT $nt; Trident/$trident)";
}

function opera($arch) {
    $op_extra = array(
        '',
        '; .NET CLR 1.1.' . rand(4320, 4325) . '',
        '; WOW64'
    );

    $presto = presto_version();
    $version = presto_version2();

    switch ($arch) {
    case 'lin':
        return "(X11; Linux {proc}; U; {lang}) Presto/$presto Version/$version";
    case 'win':
    default:
        $nt = nt_version();
        return "(Windows NT $nt; U; {lang}) Presto/$presto Version/$version";
    }
}

function chrome($arch) {
    $saf = rand(531, 536) . rand(0, 2);
    $chrome = chrome_version();

    switch ($arch) {
    case 'lin':
        return "(X11; Linux {proc}) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
    case 'mac':
        $osx = osx_version();
        return "(Macintosh; U; {proc} Mac OS X $osx) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
    case 'win':
    default:
        $nt = nt_version();
        return "(Windows NT $nt) AppleWebKit/$saf (KHTML, like Gecko) Chrome/$chrome Safari/$saf";
    }
}

/**
 * Main function which will choose random browser
 * @param  array $lang  languages to choose from
 * @return string       user agent
 */
function fake_uagent(array $lang=array('en-US')) {
    list($browser, $os) = chooseRandomBrowserAndOs();

    $proc = array(
        'lin' => array('i686', 'x86_64'),
        'mac' => array('Intel', 'PPC', 'U; Intel', 'U; PPC'),
        'win' => array('foo')
    );

    switch ($browser) {
	case 'firefox':
        $ua = "Mozilla/5.0 " . firefox($os);
        break;
    case 'safari':
        $ua = "Mozilla/5.0 " . safari($os);
        break;
    case 'iexplorer':
        $ua = "Mozilla/5.0 " . iexplorer($os);
        break;
    case 'opera':
        $ua = "Opera/" . rand(8, 9) . '.' . rand(10, 99) . ' ' . opera($os);
        break;
    case 'chrome':
        $ua = 'Mozilla/5.0 ' . chrome($os);
        break;
    }

    $ua = str_replace('{proc}', array_random($proc[$os]), $ua);
    $ua = str_replace('{lang}', array_random($lang), $ua);

    return $ua;
}
