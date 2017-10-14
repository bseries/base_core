<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use DateTimeZone;

class Timezones extends \base_core\models\BaseG11n {

	protected static function _available() {
		return explode(' ', PROJECT_TIMEZONES);
	}

	protected static function _data(array $options) {
		$data = [];

		if ($options['available'] === true) {
			$results = DateTimeZone::listIdentifiers();
		} else {
			$results = $options['available'];
		}
		foreach ($results as $result) {
			$data[$result] = [
				'id' => $result,
				'name' => $result
			];
		}
		return $data;
	}
}

?>