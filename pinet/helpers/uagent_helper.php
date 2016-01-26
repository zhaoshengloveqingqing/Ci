<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once(dirname(__FILE__).'/UAParser/Util/Logfile/ReaderInterface.php');
require_once(dirname(__FILE__).'/UAParser/Util/Converter.php');
require_once(dirname(__FILE__).'/UAParser/Util/Fetcher.php');
require_once(dirname(__FILE__).'/UAParser/Util/Logfile/AbstractReader.php');
require_once(dirname(__FILE__).'/UAParser/Util/Logfile/ApacheCommonLogFormatReader.php');
require_once(dirname(__FILE__).'/UAParser/Exception/DomainException.php');
require_once(dirname(__FILE__).'/UAParser/Exception/FetcherException.php');
require_once(dirname(__FILE__).'/UAParser/Exception/FileNotFoundException.php');
require_once(dirname(__FILE__).'/UAParser/Exception/InvalidArgumentException.php');
require_once(dirname(__FILE__).'/UAParser/Exception/ReaderException.php');
require_once(dirname(__FILE__).'/UAParser/Result/AbstractClient.php');
require_once(dirname(__FILE__).'/UAParser/Result/AbstractSoftware.php');
require_once(dirname(__FILE__).'/UAParser/Result/AbstractVersionedSoftware.php');
require_once(dirname(__FILE__).'/UAParser/Result/Client.php');
require_once(dirname(__FILE__).'/UAParser/Result/Device.php');
require_once(dirname(__FILE__).'/UAParser/Result/OperatingSystem.php');
require_once(dirname(__FILE__).'/UAParser/Result/UserAgent.php');
require_once(dirname(__FILE__).'/UAParser/AbstractParser.php');
require_once(dirname(__FILE__).'/UAParser/UserAgentParser.php');
require_once(dirname(__FILE__).'/UAParser/DeviceParser.php');
require_once(dirname(__FILE__).'/UAParser/OperatingSystemParser.php');
require_once(dirname(__FILE__).'/UAParser/Parser.php');

use UAParser\Parser;

function parse_uagent($uagent) {
	$parser = Parser::create(dirname(__FILE__).'/UAParser/regexes.php');
	return $parser->parse($uagent);
}
