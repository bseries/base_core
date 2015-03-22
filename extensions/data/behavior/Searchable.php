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

namespace base_core\extensions\data\behavior;

use Exception;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Searchable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => []
	];

	public static function searchConditions($model, Behavior $behavior, $q) {
		$conditions = [];

		if (preg_match('/^\s*$/', $q)) {
			return $conditions;
		}
		foreach ($behavior->config('fields') as $field) {
			$conditions['OR'][$field] = ['LIKE' => '%' . $q . '%'];
		}
		return $conditions;
	}
}

?>