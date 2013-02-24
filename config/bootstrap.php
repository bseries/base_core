<?php

use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\action\Request as ActionRequest;
use lithium\console\Request as ConsoleRequest;

Environment::is(function($request) {
	$isLocal = in_array($request->env('SERVER_ADDR'), array('::1', '127.0.0.1'));
	$isConsole = PHP_SAPI == 'cli';

	switch (true) {
		case ($isConsole && preg_match('#Code/clepto/main/#', $request->env('PWD'))):
		case ($isLocal || gethostname() == 'coffee.local'):
			return 'development';
		case (isset($request->env)):
			return $request->env;
		case ($isConsole && preg_match('#/new#', $request->env('PWD'))):
		case (preg_match('/^new/', $request->env('HTTP_HOST'))):
			return 'staging';
		default:
			return 'production';
	}
});
if (PHP_SAPI == 'cli') {
	Environment::set(new ConsoleRequest());
} else {
	Environment::set(new ActionRequest());
}

$config = array(
	'users' => array(
		'john' => 'foobar'
	),
	'site' => array(
		'title' => 'Application',
		'email' => 'mail@example.com',
		'phone' => '+49 123 4567'
	),
	'service' => array(
		'googleAnalytics' => array(
			// 'account' => ''
		)
	)
);
Environment::set('production', $config);
Environment::set('development', $config);

Libraries::add('temporary', array(
	'path' => dirname(__DIR__) . '/libraries/temporary/src'
));

require __DIR__ . '/bootstrap/errors.php';
require __DIR__ . '/bootstrap/action.php';
require __DIR__ . '/bootstrap/cache.php';
require __DIR__ . '/bootstrap/session.php';
// require __DIR__ . '/bootstrap/g11n.php';
require __DIR__ . '/bootstrap/media.php';

if (PHP_SAPI === 'cli') {
	require __DIR__ . '/bootstrap/console.php';
}


?>