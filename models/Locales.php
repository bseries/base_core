<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use Exception;
use lithium\g11n\Catalog;
use lithium\g11n\Locale;

// Works with language part only.
class Locales extends \base_core\models\BaseG11n {

	protected static function _available() {
		return explode(' ', PROJECT_LOCALES);
	}

	// Implements special mode for when translate is `false`. Will then
	// translate each language into its own language.
	protected static function _data(array $options) {
		$data = [];

		$results = Catalog::read(true, 'language', $options['translate'] ?: 'en');
		if ($options['available'] !== true) {
			$options['available'] = array_map(function($v) {
				return Locale::language($v);
			}, $options['available']);

			$results = array_intersect_key($results, array_fill_keys($options['available'], null));
		}
		foreach ($results as $code => $name) {
			if ($options['translate'] === false) {
				// Translate each language into its own language.
				try {
					$translated = Catalog::read(true, 'language', $code);

					if (isset($translated[$code])) {
						$name = $translated[$code];
					}
				} catch (Exception $e) {
					// es_419 is an invalid code
				}
			}
			$data[$code] = [
				'id' => $code,
				'name' => $name
			];
		}
		return $data;
	}
}

?>