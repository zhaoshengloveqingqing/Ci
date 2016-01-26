<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Sparks
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		CodeIgniter Reactor Dev Team
 * @author      Kenny Katzgrau <katzgrau@gmail.com>
 * @since		CodeIgniter Version 1.0
 * @filesource
 */

/**
 * Loader Class
 *
 * Loads views and files
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		CodeIgniter Reactor Dev Team
 * @author      Kenny Katzgrau <katzgrau@gmail.com>
 * @category	Loader
 * @link		http://codeigniter.com/user_guide/libraries/loader.html
 */
class Pinet_Loader extends CI_Loader
{
    /**
     * Keep track of which sparks are loaded. This will come in handy for being
     *  speedy about loading files later.
     *
     * @var array
     */
    var $_ci_loaded_sparks = array();

    /**
     * Is this version less than CI 2.1.0? If so, accomodate
     * @bubbafoley's world-destroying change at: http://bit.ly/sIqR7H
     * @var bool
     */
    var $_is_lt_210 = false;

    /**
     * Constructor. Define SPARKPATH if it doesn't exist, initialize parent
     */
    function __construct()
    {
        if(!defined('SPARKPATH'))
        {
            define('SPARKPATH', 'sparks/');
        }

        $this->_is_lt_210 = (is_callable(array('CI_Loader', 'ci_autoloader'))
                               || is_callable(array('CI_Loader', '_ci_autoloader')));

        parent::__construct();

		$this->_ci_modules = array(); // Added for caching all the loaded modules
		$this->_ci_widgets = array();
		$this->_ci_mixins = array();
		load_class('Module', 'core');
		load_class('Widget', 'core'); // Load the widget base class
    }

	public function mixin($mixin) {
		$this->library('entity');
        if(is_array($mixin)) {
            foreach($mixin as $m) {
                $this->mixin($m);
            }
            return;
        }

		if(isset($this->_ci_mixins[$mixin])) {
			return $this->_ci_mixins[$mixin];
		}

		foreach(array(APPPATH, 'pinet/') as $p) {
			$path = FCPATH.$p.'mixins/'.$mixin.'.php';
			if(file_exists($path)) {
				require_once($path);
				$cls = ucfirst($mixin).'_Mixin';
				if(class_exists($cls)) {
					$m = new $cls();
					$m->init(); // Initialize the mixin
					$this->_ci_mixins[$mixin] = $m;
					return $m;
				}
			}
		}
		return null;
	}

	public function widget($widget) {
        if(is_array($widget)) {
            foreach($widget as $w) {
                $this->widget($w);
            }
            return;
        }
		if(isset($this->_ci_widgets[$widget])) {
			return $this->_ci_widgets[$widget];
		}

		foreach(array(APPPATH, 'pinet/') as $name) {
			$path = FCPATH.$name.'widgets/'.$widget.'/widget.php';
			if(file_exists($path)) {
				require_once($path);
				$cls = ucfirst($widget).'_Widget';
				if(class_exists($cls)) {
					$w = new $cls();
					$w->init(); // Initialize the widget
					$this->_ci_widgets[$widget] = $w;
					return $w;
				}
			}
		}
		return null;
	}

	public function module($module) {
		if (is_array($module)) {
			foreach ($module as $babe) {
				$this->module($babe);
			}
			return;
		}

		if(isset($this->_ci_modules[$module])) {
			return $this->_ci_modules[$module];
		}

		$path = FCPATH.APPPATH.'modules/'.$module.'/controller.php';
		if(file_exists($path)) {
			require_once($path);
			$mod = ucfirst($module.'_Module');
			$mod = new $mod();
			$this->_ci_modules[$module] = $mod;
			return $mod;
		}
	}


	public function model($model, $name = '', $db_conn = FALSE)
	{
		if (is_array($model))
		{
			foreach ($model as $babe)
			{
				$this->model($babe);
			}
			return;
		}

		if ($model == '')
		{
			return;
		}

		$path = '';

		// Is the model in a sub-folder? If so, parse out the filename and path.
		if (($last_slash = strrpos($model, '/')) !== FALSE)
		{
			// The path is in front of the last slash
			$path = substr($model, 0, $last_slash + 1);

			// And the model name behind it
			$model = substr($model, $last_slash + 1);
		}

		if ($name == '')
		{
			$name = $model;
		}

		if (in_array($name, $this->_ci_models, TRUE))
		{
			return;
		}

		$CI =& get_instance();
		if (isset($CI->$name))
		{
			show_error('The model name you are loading is the name of a resource that is already being used: '.$name);
		}

		$model = strtolower($model);

		foreach ($this->_ci_model_paths as $mod_path)
		{
			if ( ! file_exists($mod_path.'models/'.$path.$model.'.php'))
			{
				continue;
			}

			if ($db_conn !== FALSE AND ! class_exists('CI_DB'))
			{
				if ($db_conn === TRUE)
				{
					$db_conn = '';
				}

				$CI->load->database($db_conn, FALSE, TRUE);
			}

			if ( ! class_exists('CI_Model') || ! class_exists('Pinet_Model'))
			{
				load_class('Model', 'core');
			}

			require_once($mod_path.'models/'.$path.$model.'.php');

			$model = ucfirst($model);

			$CI->$name = new $model();

			$this->_ci_models[] = $name;
			return;
		}

		// couldn't find the model
		show_error('Unable to locate the model you have specified: '.$model);
	}

	public function preventLoad($name) {
		$this->_base_classes [$name]= $name;
	}

    /**
     * To accomodate CI 2.1.0, we override the initialize() method instead of
     *  the ci_autoloader() method. Once sparks is integrated into CI, we
     *  can avoid the awkward version-specific logic.
     * @return Loader
     */
    function initialize()
    {
        parent::initialize();

        if(!$this->_is_lt_210)
        {
            $this->ci_autoloader();
        }

        return $this;
    }

    /**
     * Load a spark by it's path within the sparks directory defined by
     *  SPARKPATH, such as 'markdown/1.0'
     * @param string $spark The spark path withint he sparks directory
     * @param <type> $autoload An optional array of items to autoload
     *  in the format of:
     *   array (
     *     'helper' => array('somehelper')
     *   )
     * @return <type>
     */
    function spark($spark, $autoload = array())
    {
        if(is_array($spark))
        {
            foreach($spark as $s)
            {
                $this->spark($s);
            }
        }

        $spark = ltrim($spark, '/');
        $spark = rtrim($spark, '/');

        $spark_path = SPARKPATH . $spark . '/';
        $parts      = explode('/', $spark);
        $spark_slug = strtolower($parts[0]);

        # If we've already loaded this spark, bail
        if(array_key_exists($spark_slug, $this->_ci_loaded_sparks))
        {
            return true;
        }

        # Check that it exists. CI Doesn't check package existence by itself
        if(!file_exists($spark_path))
        {
            show_error("Cannot find spark path at $spark_path");
        }

        if(count($parts) == 2)
        {
            $this->_ci_loaded_sparks[$spark_slug] = $spark;
        }

        $this->add_package_path($spark_path);

        foreach($autoload as $type => $read)
        {
            if($type == 'library')
                $this->library($read);
            elseif($type == 'model')
                $this->model($read);
            elseif($type == 'config')
                $this->config($read);
            elseif($type == 'helper')
                $this->helper($read);
            elseif($type == 'view')
                $this->view($read);
            else
                show_error ("Could not autoload object of type '$type' ($read) for spark $spark");
        }

        // Looks for a spark's specific autoloader
        $this->ci_autoloader($spark_path);

        return true;
    }

	/**
	 * Pre-CI 2.0.3 method for backward compatility.
	 *
	 * @param null $basepath
	 * @return void
	 */
	function _ci_autoloader($basepath = NULL)
	{
		$this->ci_autoloader($basepath);
	}

	/**
	 * Specific Autoloader (99% ripped from the parent)
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, and helpers to be loaded automatically.
	 *
	 * @param array|null $basepath
	 * @return void
	 */
	function ci_autoloader($basepath = NULL)
	{
        if($basepath !== NULL)
        {
            $autoload_path = $basepath.'config/autoload'.EXT;
        }
        else
        {
            $autoload_path = APPPATH.'config/autoload'.EXT;
        }

        if(! file_exists($autoload_path))
        {
            return FALSE;
        }

		include($autoload_path);

		if ( ! isset($autoload))
		{
			return FALSE;
		}

        if($this->_is_lt_210 || $basepath !== NULL)
        {
            // Autoload packages
            if (isset($autoload['packages']))
            {
                foreach ($autoload['packages'] as $package_path)
                {
                    $this->add_package_path($package_path);
                }
            }
        }

        // Autoload sparks
		if (isset($autoload['sparks']))
		{
			foreach ($autoload['sparks'] as $spark)
			{
				$this->spark($spark);
			}
		}

        if($this->_is_lt_210 || $basepath !== NULL)
        {
            if (isset($autoload['config']))
            {
                // Load any custom config file
                if (count($autoload['config']) > 0)
                {
                    $CI =& get_instance();
                    foreach ($autoload['config'] as $key => $val)
                    {
                        $CI->config->load($val);
                    }
                }
            }

            // Autoload helpers and languages
            foreach (array('helper', 'language') as $type)
            {
                if (isset($autoload[$type]) AND count($autoload[$type]) > 0)
                {
                    $this->$type($autoload[$type]);
                }
            }

            // A little tweak to remain backward compatible
            // The $autoload['core'] item was deprecated
            if ( ! isset($autoload['libraries']) AND isset($autoload['core']))
            {
                $autoload['libraries'] = $autoload['core'];
            }

            // Load libraries
            if (isset($autoload['libraries']) AND count($autoload['libraries']) > 0)
            {
                // Load the database driver.
                if (in_array('database', $autoload['libraries']))
                {
                    $this->database();
                    $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
                }

                // Load all other libraries
                foreach ($autoload['libraries'] as $item)
                {
                    $this->library($item);
                }
            }

            // Autoload models
            if (isset($autoload['model']))
            {
                $this->model($autoload['model']);
            }
        }
	}
}
