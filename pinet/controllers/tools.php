<?php defined('BASEPATH') or exit('No direct script access allowed');

class Tools extends Pinet_Controller {
	public function widget($name, $folder = 'application') {
		$now = strftime('%a %b %d %H:%M:%S %Y');
		$user = ucfirst(get_current_user());
		$json = <<<EOT
{
	"author": "$user",
	"date": "$now",
	"version": "1.0",
	"name": "pinet.$name",
	"doc":"[doc]",
	"js": {
		"depends": [],
		"files" : []
	},
	"scss": {
		"depends": [],
		"files": []
	}
}
EOT;

		$class = ucfirst($name);
		$php = <<<EOT
<?php defined("BASEPATH") or exit("No direct script access allowed");

class ${class}_Widget extends Pinet_Widget {
	public function __construct() {
		parent::__construct();
	}

	public function init() {
		parent::init();
	}
}
EOT;
        if($this->input->is_cli_request()) {
			$path = $folder.'/widgets/'.$name;
			if(file_exists($path)) {
				trigger_error('The widget '.$name.' is existed!!!');
				return -1;
			}
			mkdir($path, 0755, true);
			file_put_contents($path.'/widget.json', $json);
			file_put_contents($path.'/widget.php', $php);
			echo 'Done';
		}
	}
}
