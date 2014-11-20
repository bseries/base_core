<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

define('BASE_CORE_VERSION', '1.2.0');

use lithium\core\Libraries;
use lithium\net\http\Media as HttpMedia;
use base_core\models\Assets;

if (!include LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/Libraries.php') {
	$message  = "Lithium core could not be found.  Check the value of LITHIUM_LIBRARY_PATH in ";
	$message .= __FILE__ . ".  It should point to the directory containing your ";
	$message .= "/libraries directory.";
	throw new ErrorException($message);
}

require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/Object.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/StaticObject.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/util/Collection.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/util/collection/Filters.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/util/Inflector.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/util/String.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/Adaptable.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/core/Environment.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/Message.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Message.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Media.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Request.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Response.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Route.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/net/http/Router.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/action/Controller.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/action/Dispatcher.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/action/Request.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/action/Response.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/template/View.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/template/view/Renderer.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/template/view/Compiler.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/template/view/adapter/File.php';
require LITHIUM_LIBRARY_PATH . '/unionofrad/lithium/lithium/storage/Cache.php';

Libraries::add('lithium');
Libraries::add('app', ['default' => true]);

require 'bootstrap/environment.php';
require LITHIUM_APP_PATH . '/libraries/autoload.php';

// Register any lithium libraries.
foreach (glob(LITHIUM_LIBRARY_PATH . '/li3_*') as $item) {
	Libraries::add(basename($item));
}

// Adding myself.
Libraries::add('base_core', [
	'bootstrap' => false
]);

require 'bootstrap/connections.php';
require 'bootstrap/errors.php';
require 'bootstrap/action.php';

if (PHP_SAPI !== 'cli') {
	require 'bootstrap/cache.php';
}
require 'bootstrap/session.php';
require 'bootstrap/g11n.php';
require 'bootstrap/media.php';
require 'settings.php';

if (PHP_SAPI === 'cli') {
	require 'bootstrap/console.php';
}
require 'bootstrap/auth.php';
require 'panes.php';

// ------------------------------------------------------------------------------------------------

require LITHIUM_APP_PATH . '/config/routes.php';
require 'routes.php';

require 'media.php';
require 'widgets.php';
require 'access.php';

// ------------------------------------------------------------------------------------------------

// Continue loading and bootstrapping modules. Certain modules may already been loaded. These
// must be skipped. Also we load the module types in order. Always load core modules first.

$moduleTypes = [ // This array also defines the primary order in which modules are loaded.
	'base' => 'Bento', // base modules must come first.
	'cms' => 'Bureau',
	'billing' => 'Billing',
	'ecommerce' => 'Boutique'
];
foreach ($moduleTypes as $prefix => $title) {
	$modules = array_map('basename', glob(
		LITHIUM_LIBRARY_PATH . "/{$prefix}_*",
		GLOB_BRACE | GLOB_NOSORT | GLOB_ONLYDIR
	));

	uasort($modules, function($a, $b) {
		if (strpos($a, '_core') !== false) {
			return -1;
		} elseif (strpos($b, '_core') !== false) {
			return 1;
		}
		return strcmp($a, $b);
	});

	foreach ($modules as $name) {
		if (Libraries::get($name)) {
			// Certain modules may already been loaded (i.e. base_core) during the bootstrap
			// process above. Prevent loading them and their config files a second time.
			continue;
		}
		Libraries::add($name, [
			'bootstrap' => false // Modules bootstrap not needed. See below.
		]);

		// Now auto load files from the modules config directories in order.
		$path = Libraries::get($name, 'path');

		$available = [
			'routes',
			'settings',
			'media',
			'jobs',
			'panes',
			'widgets',
			'contents',
			'misc'
		];
		foreach ($available as $config) {
			if (file_exists($file = $path . "/config/{$config}.php")) {
				require_once $file;
			}

			if (file_exists($path . "/config/bootstrap.php")) {
				trigger_error(
					"Found deprecated bootstrap file in module `{$name}`.",
					E_USER_DEPRECATED
				);
			}
		}
	}
}

?>