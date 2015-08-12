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

use lithium\g11n\Catalog;

class Locales extends \base_core\models\G11nBase {

	protected static function _available() {
		return explode(' ', PROJECT_LOCALES);
	}

	protected static function _data(array $options) {
		$data = [];

		if ($options['translate']) {
			$results = Catalog::read(true, 'language', $options['translate']);
		}
		foreach ($options['available'] as $available) {
			if (!$options['translate']) {
				$results = Catalog::read(true, 'language', $available);
			}
			$data[$available] = [
				'id' => $available,
				'name' => $results[$available]
			];
		}
		return $data;
	}
}

?>