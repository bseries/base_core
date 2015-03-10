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

//
// The `INSIDE_ADMIN` constant allows us to apply some optimization and not
// load certain parts of the framework when we're navigating through the
// we app part.
//
// Note: As this is a three-way switch strict comparison is needed.
//
if (PHP_SAPI === 'cli') {
	define('INSIDE_ADMIN', null);
} elseif (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
	define('INSIDE_ADMIN', true);
} else {
	define('INSIDE_ADMIN', false);
}

//
// This bootstrap file boots the application as well as any libraries, plugins or modules.
//
// ## Environment
// We are using our own environment handling through .env files. This replaces
// the lithium environment handling, which may - in future - go away.
//
// ## Loading
// There are 3 groups of libraries (lithium libraries, bento modules, composer libraries)
// that are loaded depending on their type by either of the 2 available autoloaders (lithium
// or composer). Both lithium libaries and bento modules are loaded through lithium's autoloader
// all other make use of the composer autoloader.
//
// ## Configurations
// During bootstrap configuration is loaded from the modules and the app. The configuration
// file names are defined formally by the bootstrap function `bootstrapFormal()` below. This
// somewhat differs from common lithium app's and libraries.
//
// ### Routes
// Route configuration is handled in a special way as routes defined first will match
// first. App routes are always loaded first, then explicit module routes and at the
// very last the most generic module routes. Modules should never define app routes.
//
// ### Settings
// Modules don't have access to settings to the app's settings during configuration. The
// app itself has access to default settings as defined in the modules configuration. This
// is to prevent modules from overwriting the app's settings.

//
// Function definitions.
//

$defineFromDotEnvFile = function($file) {
	$fh = fopen($file, 'r');
	$results = [];

	while (!feof($fh)) {
		$line = fgets($fh);

		if (!preg_match('/(?:export )?([a-zA-Z_][a-zA-Z0-9_]*)=(.*)/', $line, $matches)) {
			continue;
		}
		$key = $matches[1];
		$value = trim($matches[2], '"\'');

		switch ($value) {
			case 'y':
			case 'yes':
			case 'true':
				$value = true;
				break;
			case 'n':
			case 'no':
			case 'false':
				$value = false;
				break;
		}
		define('PROJECT_' . $key, $value);
	}

	fclose($fh);
	return $results;
};

// Implements a boostraping function that replaces the common lithium bootstraping for
// modules and app.
$bootstrapFormal = function($name, $path) {
	if ($name !== 'app') {
		$available = [
			'version',
			'routes',
			'settings',
			'media',
			'jobs',
			'panes',
			'widgets',
			'contents',
			'g11n',
			'misc'
		];
		if (INSIDE_ADMIN === false) {
			// Keep order when unsetting.
			$available = array_diff($available, [
				'routes',
				'jobs',
				'panes',
				'widgets',
				'g11n'
			]);
		}
	} else {
		// Load app configuration last, so it can overwrite module default configuration and
		// isn't overwritten by anything else.
		$available = [
			// App routes have already been loaded.
			'settings',
			'media',
			'access',
			'switchboard',
			'base',
			'cms',
			'billing',
			'ecommerce'
		];
	}

	foreach ($available as $config) {
		if (file_exists($file = $path . "/config/{$config}.php")) {
			require_once $file;
		}
	}

	// Configuration deprecations.
	// @deprecated
	if ($name !== 'base_core' && file_exists($path . "/config/bootstrap.php")) {
		trigger_error(
			"Found deprecated bootstrap file in `{$name}`.",
			E_USER_DEPRECATED
		);
	}
};

//
// Preparing the environment.
//

// Load the currently active environment file from the project's root/config directory.
// Assumes we are located inside `project/app/libraries/base_core/config`. Any variables
// defined inside the env file are prefixed with `PROJECT_`.
$defineFromDotEnvFile(dirname(dirname(dirname(dirname(__DIR__)))) . '/config/current.env');

// Define some lithium internal constants. We won't use them ourserselves as they are
// planned to go away in future lithium versions.
define('LITHIUM_APP_PATH', PROJECT_PATH . '/app');
define('LITHIUM_LIBRARY_PATH', PROJECT_PATH . '/app/libraries');

//
// Lithium library loading.
//

// Preload some classes always used to increase performance.
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/core/Object.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/core/StaticObject.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/util/Collection.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/util/collection/Filters.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/util/Inflector.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/util/String.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/core/Adaptable.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/core/Environment.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/Message.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Message.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Media.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Request.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Response.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Route.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/net/http/Router.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/action/Controller.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/action/Dispatcher.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/action/Request.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/action/Response.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/template/View.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/template/view/Renderer.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/template/view/Compiler.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/template/view/adapter/File.php';
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/storage/Cache.php';

// Make lithium's autoloader class available and initialize composer's autoloader. Composer's
// autoloader is by default aware of all its libraries through a statically generated file.
// Lithium's autoloader must be told of them via `Libaries::add()`.
require PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/core/Libraries.php';
require PROJECT_PATH . '/app/libraries/autoload.php';

use lithium\core\Libraries;

Libraries::add('lithium');

// Must add the app here as lithium plugins may rely on its path being accessible.
Libraries::add('app', [
	'default' => true,
	'bootstrap' => false
]);

// Make lithium understand our environment management.
require 'bootstrap/environment.php';

// Register any lithium libraries. These must come before
// loading any other bento modules as they possibly make
// use of them.
foreach (glob(PROJECT_PATH . '/app/libraries/li3_*') as $item) {
	Libraries::add(basename($item));
}

require 'bootstrap/connections.php';
require 'bootstrap/errors.php';
require 'bootstrap/action.php';

if (PHP_SAPI !== 'cli') {
	require 'bootstrap/cache.php';
	require 'bootstrap/session.php';
}
require 'bootstrap/g11n.php';
require 'bootstrap/media.php';

if (PHP_SAPI === 'cli') {
	require 'bootstrap/console.php';
}
require 'bootstrap/auth.php';
require 'bootstrap/access.php';
require 'bootstrap/mail.php';

// ------------------------------------------------------------------------------------------------

require PROJECT_PATH . '/app/config/routes.php';

// ------------------------------------------------------------------------------------------------

//
// Loading modules.
//

// Continue loading and bootstrapping modules. Also we load the module types in order. Always
// load core modules first.

$moduleTypes = [ // This array also defines the primary order in which modules are loaded.
	'base' => 'Bento', // base modules must come first.
	'cms' => 'Bureau',
	'billing' => 'Billing',
	'ecommerce' => 'Boutique'
];

foreach ($moduleTypes as $prefix => $title) {
	$modules = array_map('basename', glob(
		PROJECT_PATH . "/app/libraries/{$prefix}_*",
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
		Libraries::add($name, [
			'bootstrap' => false
		]);
		$bootstrapFormal($name, PROJECT_PATH . '/app/libraries/' . $name);
	}
}

// ------------------------------------------------------------------------------------------------

//
// Loading the app.
//

$bootstrapFormal('app', PROJECT_PATH . '/app');

// ------------------------------------------------------------------------------------------------

require 'bootstrap/routes.php';

?>