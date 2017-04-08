<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
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