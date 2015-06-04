<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\models;

class Timezones extends \base_core\models\G11nBase {

	protected static function _available() {
		return explode(' ', PROJECT_TIMEZONES);
	}

	protected static function _data(array $options) {
		$data = [];

		foreach ($options['available'] as $available) {
			$data[$available] = [
				'id' => $available,
				'name' => $available
			];
		}
		return $data;
	}
}

?>