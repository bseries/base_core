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

namespace base_core\models;

use InvalidArgumentException;
use OutOfBoundsException;
use lithium\core\Environment;
use lithium\util\Set;

// Usable in conjunction with an entity having an `url` property, depends
// on a model using the UrlTrait.
trait SchemeTrait {

	protected static $_schemes = [];

	// requires a protected static $_defaultScheme property.

	public static function registerScheme($scheme, array $options = []) {
		if (isset(static::$_schemes[$scheme])) {
			$default = static::$_schemes[$scheme];
		} else {
			$default = static::$_defaultScheme;
		}
		static::$_schemes[$scheme] = Set::merge($default, $options);
	}

	public static function registeredScheme($scheme, $capability) {
		if (!isset(static::$_schemes[$scheme])) {
			throw new OutOfBoundsException("No registered scheme `{$scheme}`.");
		}
		return static::$_schemes[$scheme][$capability];
	}

	public static function hasRegisteredScheme($scheme) {
		return isset(static::$_schemes[$scheme]);
	}

	public function can($entity, $capability) {
		$scheme = $entity->scheme();

		if (!isset(static::$_schemes[$scheme])) {
			throw new OutOfBoundsException("No registered scheme `{$scheme}`.");
		}
		return static::$_schemes[$scheme][$capability];
	}

	// Calculates the base URL from registered schemes.
	//
	// $scheme may either be a string, an array of available schemes or
	// an \lithium\net\http\Request object, to auto negotatiate the best
	// correct HTTP scheme.
	public static function base($scheme) {
		if (is_object($scheme)) {
			if ($scheme->is('ssl')) {
				// Require https for SSL requests. Otherwise page will be
				// broken. Will throw exception further down.
				$available = ['https'];
			} else {
				// When requests is not SSL prefer http over https.
				$available = [];

				if (isset(static::$_schemes['http'])) {
					$available[] = 'http';
				}
				if (isset(static::$_schemes['https'])) {
					$available[] = 'https';
				}
			}
		} else {
			$available = (array) $scheme;
		}
		foreach ($available as $s) {
			if (!isset(static::$_schemes[$s])) {
				throw new OutOfBoundsException("No registered scheme `{$s}`.");
			}
			if (empty(static::$_schemes[$s]['base'])) {
				continue;
			}
			$bases = static::$_schemes[$s]['base'];
			return is_array($bases) ? $bases[Environment::get()] : $bases;
		}
		$message = 'No base found for scheme/s: ' . var_export($scheme, true);
		throw new InvalidArgumentException($message);
	}
}

?>