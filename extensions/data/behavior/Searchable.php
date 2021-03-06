<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Searchable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => []
	];

	public static function searchQuery($model, Behavior $behavior, $q, array $query = []) {
		$query += [
			'conditions' => [],
			'with' => []
		];
		if (preg_match('/^\s*$/', $q)) {
			return $query;
		}

		foreach ($behavior->config('fields') as $field) {
			// Enable relations if we're searching by a relation's field.
			if (preg_match('/^(.*)\./', $field, $matches)) {
				$query['with'][] = $matches[1];
			}

			switch ($model::schema($field)['type']) {
				case 'date':
				case 'datetime':
					// Might have partial dates
					// Also dates need to be converted into default format.
					$field = static::_qualifyField($model, $behavior, $field);
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

						$query['conditions']['OR'][] = [
							'AND' => [
								'YEAR(' . $field . ')' => $year,
								'MONTH(' . $field . ')' => $month
							]
						];
					} else {
						// Cast to object to skip database formatters, forcing
						// string to be converted to (invalid) date.
						$query['conditions']['OR'][] = (object) ('(' . $field . ' LIKE \'%' . $q . '%\')');
					}
					break;
				case 'integer':
				case 'float':
					if (!is_numeric($q)) {
						continue;
					}
					$field = static::_qualifyField($model, $behavior, $field);
					$query['conditions']['OR'][$field] = $q;
				break;
				case 'string':
				default:
					$field = static::_qualifyField($model, $behavior, $field);
					$query['conditions']['OR'][$field] = ['LIKE' => '%' . $q . '%'];
				break;
			}
		}
		$query['with'] = array_unique($query['with']);
		return $query;
	}

	// Fully qualify to prevent ambigous columns.
	protected static function _qualifyField($model, $behavior, $field) {
		if (preg_match('/^(.*)\./', $field, $matches)) {
			return $field;
		}
		return basename(str_replace('\\', '/', $model)) . '.' . $field;
	}
}

?>