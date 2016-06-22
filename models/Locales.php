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

			$results = array_intersect_assoc($results, array_fill_keys($options['available'], null));
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