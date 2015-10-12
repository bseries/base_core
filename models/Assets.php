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

use InvalidArgumentExeption;
use OutOfBoundsException;
use lithium\core\Environment;
use lithium\util\Set;

class Assets extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_schemes = [];

	// Calculates the base URL from registered schemes.
	//
	// $scheme may either be a string, an array of available schemes or
	// an \lithium\net\http\Request object, to auto negotatiate the best
	// possible HTTP scheme.
	public static function base($scheme) {
		if (is_object($scheme)) {
			$available = [$scheme->is('ssl') ? 'https' : 'http'];
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
		throw new InvalidArgumentExcpetion($message);
	}

	public static function registerScheme($scheme, array $options = []) {
		if (isset(static::$_schemes[$scheme])) {
			$default = static::$_schemes[$scheme];
		} else {
			$default = [
				'base' => false
			];
		}
		static::$_schemes[$scheme] = Set::merge($default, $options);
	}
}

?>