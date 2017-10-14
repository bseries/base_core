<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\cms;

use lithium\util\Set;

class Settings extends \lithium\core\StaticObject {

	protected static $_data = [];

	public static function register($name, $default = null) {
		static::$_data = Set::merge(static::$_data, Set::expand([$name => $default]));
	}

	public static function write($name, $data) {
		$current = static::read($name);

		if (is_array(($current)) && is_numeric(key($current))) {
			// Prevent merging list like settings.
			static::$_data = array_replace_recursive(
				static::$_data,
				Set::expand([$name => $data])
			);
		} else {
			static::$_data = Set::merge(
				static::$_data,
				Set::expand([$name => $data])
			);
		}
	}

	public static function read($name = null) {
		if (!$name) {
			return static::$_data;
		}
		return static::_processDotPath($name, static::$_data);
	}

	protected static function _processDotPath($path, &$arrayPointer) {
		if (isset($arrayPointer[$path])) {
			return $arrayPointer[$path];
		}
		if (strpos($path, '.') === false) {
			return null;
		}
		$pathKeys = explode('.', $path);
		foreach ($pathKeys as $pathKey) {
			if (!is_array($arrayPointer) || !isset($arrayPointer[$pathKey])) {
				return false;
			}
			$arrayPointer = &$arrayPointer[$pathKey];
		}
		return $arrayPointer;
	}
}

?>