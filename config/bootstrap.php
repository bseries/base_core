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
use base_core\extensions\cms\Features;
use base_core\models\Assets;
use lithium\net\http\Media as HttpMedia;

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

require LITHIUM_APP_PATH . '/config/environment.php';
require LITHIUM_APP_PATH . '/libraries/autoload.php';

// Register any lithium libraries.
foreach (glob(LITHIUM_LIBRARY_PATH . '/li3_*') as $item) {
	Libraries::add(basename($item));
}

// Adding myself.
Libraries::add('base_core', [
	'bootstrap' => false
]);

require LITHIUM_APP_PATH . '/config/connections.php';
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

require 'media.php';
require 'widgets.php';

use li3_access\security\Access;

Access::config([
	'entity' => [
		'adapter' => 'Rules',
		'allowAny' => true // When at least one rule matches succeed.
	]
]);
$rules = Access::adapter('entity');

$rules->add('user.role:admin', function($user, $entity, $options) {
	return $user->role == 'admin';
});
$rules->add('any', function($user, $entity, $options) {
	return true;
});

// ------------------------------------------------------------------------------------------------

// Must come after base_core but before any other libraries.
Libraries::add('base_media');

// Continue loading and bootstrapping modules. Certain modules may already been loaded. These
// must be skipped. Also we load the module types in order. Always load core modules first.

$modules = glob(
	LITHIUM_LIBRARY_PATH . '/{base,cms,ecommerce,billing}_*',
	GLOB_BRACE | GLOB_NOSORT | GLOB_ONLYDIR
);
// Alphabetically sort entriesbut always put _core modules top.
uasort($modules, function($a, $b) {
	if (strpos($a, '_core') !== false) {
		return -1;
	}
	if (strpos($b, '_core') !== false) {
		return 1;
	}
	return strcmp($a, $b);
});
$modules = array_map('basename', $modules);

foreach ($modules as $name) {
	if (Libraries::get($name)) {
		// Certain modules may already been loaded (i.e. base_core) during the bootstrap
		// process above. Prevent loading them and their config files a second time.
		continue;
	}
	Libraries::add($name);
}

?>