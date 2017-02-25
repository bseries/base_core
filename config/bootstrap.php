<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace base_core\config;

use base_core\core\Boot;
require dirname(__DIR__) . '/core/Boot.php';

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

// Implements a boostraping function that replaces the common lithium bootstraping for
// modules and app.
$bootstrapFormal = function($name, $path) {
	if ($name !== 'app') {
		$available = [
			'access' => null,
			'routes' => null,
			'panes' => [ // module only
				'*.config.access',
				// Each lib contains its own g11n configuration and
				// panes are translated.
				'libraries.' . $name . '.config.g11n'
			],
			'widgets' => null, // module only
			'switchboard' => [
				// Filters are disabled/enabled depending on certain settings.
				'*.config.base' => 'optional',
				'*.config.cms' => 'optional',
				'*.config.billing' => 'optional',
				'*.config.ecommerce' => 'optional'
			],
			'jobs' => [
				// Jobs are disabled/enabled depending on certain settings.
				'*.config.base' => 'optional',
				'*.config.cms' => 'optional',
				'*.config.billing' => 'optional',
				'*.config.ecommerce' => 'optional'
			],
			'base' => null,
			'cms' => ['*.config.base'],
			'billing' => ['*.config.base'],
			'ecommerce' => ['*.config.base', '*.config.billing']
		];
		if (INSIDE_ADMIN === false) {
			// Don't load certain module configurations when
			// not inside admin. Keep order when unsetting.
			$available = array_diff_key($available, [
				'panes' => null,
				'widgets' => null,
				'jobs' => null,
				// Intentionally missing:
				// - g11n must always be loaded as modules may contain
				//   translations for validation messages used by the app.
				// - routes must always be loaded as we may want to
				//   link back into admin for admin users from the app.
			]);
		}

		// base_core routes come first then,
		// TODO _core routes then anything else.
		if ($name !== 'base_core') {
			$available['routes'] = ['libraries.base_core.config.routes'];
		}
	} else {
		// Load app configuration last, so it can overwrite module default configuration and
		// isn't overwritten by anything else.
		$available = [
			'routes' => ['libraries.*.config.routes'],
			'access' => ['libraries.*.config.access' => 'optional'],
			'switchboard' => null,
			'jobs' => null,
			'base' => [
				'libraries.*.config.base'
			],
			'cms' => [
				'app.config.base' => 'optional',
				'libraries.*.config.cms' => 'optional'
			],
			'billing' => [
				'app.config.base' => 'optional',
				'libraries.*.config.billing' => 'optional'
			],
			'ecommerce' => [
				'app.config.base' => 'optional',
				'app.config.billing' => 'optional',
				'libraries.*.config.ecommerce' => 'optional'
			],
		];

		// Load debug configuration only when debug is enabled, so debugging tools are not
		// mistakenly enabled in production, where they might reveal precious secrets. We
		// also try to ensure to load debugging is early as possible, so early errors can
		// be catched.
		if (PROJECT_DEBUG) {
			$available = ['debug' => null] + $available;
		}
		if (INSIDE_ADMIN === true) {
			$available = array_diff_key($available, [
				'routes' => null,
				'jobs' => null
			]);
		}
	}

	// Deprecated: now defined via VERSION.txt
	if (file_exists($file = $path . '/config/version.php')) {
		trigger_error("Found deprecated configuration file `{$file}` in `{$name}`.", E_USER_DEPRECATED);
	}

	if (file_exists($file = $path . '/VERSION.txt')) {
		define(strtoupper($name) . '_VERSION', file_get_contents($file));
	}
	if (is_dir($path . '/resources/g11n/po')) {
		Boot::add(
			($name !== 'app' ? 'libraries.' . $name : $name) . '.config.g11n',
			null,
			function () use ($name, $path) {
				\lithium\g11n\Catalog::config([
					$name => [
						'adapter' => 'Gettext',
						'path' => $path . '/resources/g11n/po'
					 ]
				] + \lithium\g11n\Catalog::config());
			}
		);
	}
};

//
// Preparing the environment.
//

// Load the currently active environment file from the project's root/config directory.
// Assumes we are located inside `project/app/libraries/base_core/config`. Any variables
// defined inside the env file are prefixed with `PROJECT_`.
$root = dirname(dirname(dirname(dirname(__DIR__))));
Boot::environment($root . '/Envfile', 'PROJECT');

if (defined('PROJECT_PATH')) {
	$message = "Project path is not used anymore: do not define in Envfile.";
	trigger_error($message, E_USER_DEPRECATED);
} else {
	define('PROJECT_PATH', $root);
}
if (defined('PROJECT_VERSION_BUILD')) { // Deprecated
	$message = "Project version build is not used anymore: do not define in Envfile.";
	trigger_error($message, E_USER_DEPRECATED);
}
if (defined('PROJECT_VERSION')) { // Deprecated
	$message = "Project version should not be defined in the Envfile anymore but in VERSION.txt.";
	trigger_error($message, E_USER_DEPRECATED);
} else {
	if (file_exists($file = $root . '/VERSION.txt')) {
		define('PROJECT_VERSION', file_get_contents($file));
	} else {
		trigger_error("Failed to define project version: missing VERSION.txt file at `{$file}`.", E_USER_WARNING);
	}
}
if (defined('PROJECT_MAIL_FROM')) {
	$message = "Project MAIL_FROM is not used anymore: do not define in Envfile.";
	if (PROJECT_MAIL_FROM != 'noreply@' . PROJECT_MAIL_DOMAIN) {
		$message .= " WARNING: noreply@ is now the default but current value is " . PROJECT_MAIL_FROM;
		$message .= " Ensure you set the sender in Controller code manually.";
	}
	trigger_error($message, E_USER_DEPRECATED);
}

// Define some lithium internal constants. We won't use them ourserselves as they are
// planned to go away in future lithium versions.
define('LITHIUM_APP_PATH', PROJECT_PATH . '/app');
define('LITHIUM_LIBRARY_PATH', PROJECT_PATH . '/app/libraries');

//
// Lithium library loading.
//

// Preload some classes always used to increase performance.
require PROJECT_PATH . '/app/libraries/lithium/core/Object.php';
require PROJECT_PATH . '/app/libraries/lithium/core/StaticObject.php';
require PROJECT_PATH . '/app/libraries/lithium/util/Collection.php';
require PROJECT_PATH . '/app/libraries/lithium/util/collection/Filters.php';
require PROJECT_PATH . '/app/libraries/lithium/util/Inflector.php';
require PROJECT_PATH . '/app/libraries/lithium/util/String.php';
require PROJECT_PATH . '/app/libraries/lithium/core/Adaptable.php';
require PROJECT_PATH . '/app/libraries/lithium/core/Environment.php';
require PROJECT_PATH . '/app/libraries/lithium/net/Message.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Message.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Media.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Request.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Response.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Route.php';
require PROJECT_PATH . '/app/libraries/lithium/net/http/Router.php';
require PROJECT_PATH . '/app/libraries/lithium/action/Controller.php';
require PROJECT_PATH . '/app/libraries/lithium/action/Dispatcher.php';
require PROJECT_PATH . '/app/libraries/lithium/action/Request.php';
require PROJECT_PATH . '/app/libraries/lithium/action/Response.php';
require PROJECT_PATH . '/app/libraries/lithium/template/View.php';
require PROJECT_PATH . '/app/libraries/lithium/template/view/Renderer.php';
require PROJECT_PATH . '/app/libraries/lithium/template/view/Compiler.php';
require PROJECT_PATH . '/app/libraries/lithium/template/view/adapter/File.php';
require PROJECT_PATH . '/app/libraries/lithium/storage/Cache.php';

// Make lithium's autoloader class available and initialize composer's autoloader. Composer's
// autoloader is by default aware of all its libraries through a statically generated file.
// Lithium's autoloader must be told of them via `Libaries::add()`.
require PROJECT_PATH . '/app/libraries/lithium/core/Libraries.php';
require PROJECT_PATH . '/app/libraries/autoload.php';

use lithium\core\Libraries;

Libraries::add('lithium');

// Must add the app here as lithium plugins may rely on its path being accessible.
Libraries::add('app', [
	'default' => true,
	'bootstrap' => false
]);

// Make base_core libraries available in app bootstrap files
// (i.e. ClientRouter in app's routes.php).
Libraries::add('base_core', [
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
require 'bootstrap/cache.php';

if (PHP_SAPI !== 'cli') {
	require 'bootstrap/session.php';
}
require 'bootstrap/g11n.php';
require 'bootstrap/media.php';

if (PHP_SAPI === 'cli') {
	require 'bootstrap/console.php';
}
require 'bootstrap/security.php';
require 'bootstrap/auth.php';
require 'bootstrap/access.php';

if (FEATURE_MAIL) {
	require 'bootstrap/mail.php';
}

// ------------------------------------------------------------------------------------------------

//
// Loading modules.
//

// Continue loading and bootstrapping modules. Also we load the module types in order. Always
// load core modules first.

$modules = glob(
	PROJECT_PATH . '/app/libraries/{base,cms,billing,ecommerce}_*',
	GLOB_BRACE | GLOB_NOSORT | GLOB_ONLYDIR
);
foreach ($modules as $path) {
	if (basename($path) !== 'app' && basename($path) !== 'base_core') {
		Libraries::add(basename($path), [
			'bootstrap' => false
		]);
	}
	$bootstrapFormal(basename($path), $path);
}

// ------------------------------------------------------------------------------------------------

//
// Loading the app.
//

$bootstrapFormal('app', PROJECT_PATH . '/app');

// ------------------------------------------------------------------------------------------------

\base_core\core\Boot::run();

// ------------------------------------------------------------------------------------------------

// @deprecated
if (!\base_core\base\Sites::registry(true)->count()) {
	trigger_error('No Site found, at least one Site must be registed.', E_USER_DEPRECATED);
	\base_core\base\Sites::register(PROJECT_DOMAIN, [
		'title' => PROJECT_DOMAIN,
		'fqdn' => PROJECT_DOMAIN
	]);
}

?>