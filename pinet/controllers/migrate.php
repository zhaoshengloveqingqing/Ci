<?php defined("BASEPATH") or exit("No direct script access allowed");

class Migrate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library(array('migration', 'my_migration'));
    }

    public function version($version) {
        if($this->input->is_cli_request()) {
           $migration = $this->migration->version($version);
           if(!$migration) {
               echo $this->migration->error_string();
           }
           else {
               echo 'Migration(s) done'.PHP_EOL;
           }
       }
       else {
           show_error('You don\'t have permission for this action');;
      }
    }
 }
