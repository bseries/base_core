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

	public static function current(\lithium\action\Request $request) {
		if (!$request->env('HTTP_HOST')) {
			return null;
		}
		foreach (static::$_registry as $name => $site) {
			if ($site->fqdn() === $request->env('HTTP_HOST')) {
				return $site;
			}
		}
		return null;
	}
}

?>