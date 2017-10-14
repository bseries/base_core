<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use InvalidArgumentException;
use OutOfBoundsException;
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

	public static function registeredScheme($scheme) {
		if (!isset(static::$_schemes[$scheme])) {
			throw new OutOfBoundsException("No registered scheme `{$scheme}`.");
		}
		return static::$_schemes[$scheme];
	}

	public function can($entity, $capability) {
		$scheme = $entity->scheme();

		if (!isset(static::$_schemes[$scheme])) {
			throw new OutOfBoundsException("No registered scheme `{$scheme}`.");
		}
		return static::$_schemes[$scheme][$capability];
	}

	// Calculates the base URL from registered schemes. Will return the base
	// including the scheme prefix.
	public static function base($scheme) {
		$scheme = static::_negotiateScheme($scheme);
		return $scheme . '://' . static::$_schemes[$scheme]['base'];
	}

	// $scheme may either be a string, an array of available schemes or
	// an \lithium\net\http\Request object, to auto negotatiate the best
	// correct HTTP scheme.
	protected static function _negotiateScheme($scheme) {
		if (is_object($scheme)) {
			if ($scheme->is('ssl')) {
				// Require https for SSL requests. Otherwise page will be
				// broken.
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
		} elseif (is_string($scheme)) {
			$available = (array) $scheme;
		} elseif (is_array($scheme)) {
			$available = $scheme;
		} else {
			$available = [];
		}
		if (!$available) {
			throw new Exception('No schemes available for negotiation.');
		}
		foreach ($available as $s) {
			if (isset(static::$_schemes[$s])) {
				return $s;
			}
		}
		$message  = 'Failed to negotiate scheme using schemes `' . implode(', ', $available) . '`.';
		$message .= 'None of them are registered.';
		throw new Exception($message);
	}
}

?>