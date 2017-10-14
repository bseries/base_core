<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\core;

use OutOfRangeException;
use lithium\util\Collection;

/**
 * Trait to turn a static class into a keyed object registry. Using the registry methods
 * you add (`register()`), remove (`unregister()`) or enumerate (`registry()`) objects
 * in the registry.
 *
 * What these objects actually are is left up to the implementator. This makes the trait
 * generically useful.
 *
 * Classes making use of this trait should be named in plural form i.e. `Connections`,
 * `Caches`.
 */
trait Registerable {

	protected static $_registry = [];

	public static function registry($name) {
		if ($name === true) {
			return new Collection(['data' => static::$_registry]);
		}
		if (!isset(static::$_registry[$name])) {
			throw new OutOfRangeException("No configuration `{$name}` available.");
		}
		return static::$_registry[$name];
	}

	/**
	 * Registers a single object by name.
	 *
	 * @param string $name The name by which this configuration is referenced. Use this name to
	 *        later retrieve the configuration again using `Configurable::get()`.
	 * @param mixed $object Most commonly this is an array of configuration or an
	 *        initialized instance.
	 * @return void
	 */
	public static function register($name, $object) {
		static::$_registry[$name] = $object;
	}

	/**
	 * Unregisters a single object.
	 *
	 * @param string $name The object name.
	 * @return void
	 */
	public static function unregister($name) {
		unset(static::$_registry[$name]);
	}
}

?>