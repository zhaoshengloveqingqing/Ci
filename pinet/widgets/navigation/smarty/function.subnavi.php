<?php defined('BASEPATH') OR exit('No direct script access allowed');

function smarty_function_subnavi($params, $template) {
	$CI = &get_instance();
	$navigations = $CI->navigation->getNavigations();
	foreach($navigations as $n) {
		if($n->current) {
			$current = $n;
			break;
		}
	}
	if(isset($n)) {
		$template->tpl_vars['current_navi'] = new Smarty_Variable($n);
		$nav = <<<TEXT
	<ul class='pinet-subnavi first'>
		<li>
			<h3>{lang}{\$current_navi->label}{/lang}</h3>
            <ul class="pinet-subnavi last">
            	{foreach \$current_navi->subnavi as \$menu}
					<li>
            			{action obj=\$menu}
            		</li>
            	{/foreach}
			</ul>
		</li>
	</ul>
TEXT;
		return $template->fetch('string:'.$nav);
	}
}
