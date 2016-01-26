<?php defined('BASEPATH') or exit('No direct script access allowed');

function smarty_function_navigation($params, $template) {
	$nav = <<<TEXT
    <div id="pinet-nav" class="pinet-navigation">
      	<nav class='navigations' role="navigation">
            {section name=i loop=\$navigations}
                {action obj=\$navigations[i] type='main'}
            {/section}
			<a class="divide">
			. . .
			</a>
			<a class="language" href="" data-toggle="tooltip" data-placement="right" title="Tooltip on right">
			{picture path='/responsive/size' alt='\$title' src='language.png'}
			</a>
			<a class="logoout" href="{site_url url='welcome/logout'}" data-toggle="tooltip" data-placement="right" title="Tooltip on right">
			{picture path='/responsive/size' alt='\$title' src='logout.png'}
			</a>
      	</nav>
    </div>
TEXT;
	return $template->fetch('string:'.$nav);
}
