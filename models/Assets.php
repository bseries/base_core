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

namespace base_core\models;

use lithium\core\Environment;
use lithium\util\Set;

class Assets extends \base_core\models\Base {

	protected $_meta = array(
		'connection' => false
	);

	protected static $_schemes = [];

	public static function base($scheme) {
		$bases = static::$_schemes[$scheme]['base'];
		return is_array($bases) ? $bases[Environment::get()] : $bases;
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