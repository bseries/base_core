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
			switch ($model::schema($field)['type']) {
				case 'date':
					// Might have partial dates
					// Also dates need to be converted into default format.
					$field = $model::connection()->name($field);

					// (0)MM.(YY)YY
					if (preg_match('/^([0-9]+)\.([0-9]+)$/', $q, $matches)) {
						$month = $matches[1];
						$year = $matches[2];

						// Fix two digit year.
						if (strlen($year) === 2) {
							$year = '20' . $year;
						}
						$month = ltrim($month, '0');

						$conditions['OR'][] = [
							'AND' => [
								'YEAR(' . $field . ')' => $year,
								'MONTH(' . $field . ')' => $month
							]
						];
					} else {
						// Cast to object to skip database formatters, forcing
						// string to be converted to (invalid) date.
						$conditions['OR'][] = (object) ('(' . $field . ' LIKE \'%' . $q . '%\')');
					}
					break;
				default:
					$conditions['OR'][$field] = ['LIKE' => '%' . $q . '%'];
				break;
			}
		}
		return $conditions;
	}
}

?>