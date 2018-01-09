<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2018 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use DateTime;

class Base extends \lithium\data\Model {

	use \li3_behaviors\data\model\Behaviors;

	public static function pdo() {
		return static::connection()->connection;
	}

	public static function enum($field, array $map = []) {
		if (!isset(static::$enum[$field])) {
			return false;
		}
		$result = [];

		foreach (static::$enum[$field] as $value) {
			if (isset($map[$value])) {
				$result[$value] = $map[$value];
			} else {
				$result[$value] = $value;
			}
		}
		return $result;
	}

	public function date($entity) {
		return DateTime::createFromFormat('Y-m-d H:i:s', $entity->created);
	}
}

?>