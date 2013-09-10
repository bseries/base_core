<?php

use lithium\storage\Session;
use lithium\core\Environment;

Session::config([
	'default' => ['adapter' => 'Php', 'session.name' => Environment::get('project.name')]
	// 'default' => array(
	// 	'adapter' => 'Cookie',
	// 	'strategies' => array(
	// 		'Hmac' => array('secret' => Environment::get('security.cookieSecret'))
	// 	)
	// )
]);

?>