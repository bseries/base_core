<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\base;

use base_core\base\Site;

class Sites {

	use \base_core\core\Registerable;
	use \base_core\core\RegisterableEnumeration;

	public static function register($name, array $object) {
		static::$_registry[$name] = new Site($object);
	}

	// Returns the currently active site by looking at the given  request information.
	// The `www` prefix will be ignored, thus `example.com` will be detected even when
	// `www.example.com` is requested and vice versa.
	public static function current(\lithium\action\Request $request) {
		if (!$host = $request->env('HTTP_HOST')) {
			return null;
		}
		$requested = new Site(['fqdn' => $host]);

		foreach (static::$_registry as $name => $site) {
			if ($site->fqdn('drop') === $requested->fqdn('drop')) {
				return $site;
			}
		}
		return null;
	}
}

?>