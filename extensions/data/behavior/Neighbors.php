<?php
/**
 * Base Core
 *
 * Copyright (c) 2014 Atelier Disko - All rights reserved.
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

namespace base_core\extensions\data\behavior;

use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;
use lithium\util\Set;
use lithium\util\Collection;
use Exception;

class Neigbors extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [];

	// Retrieves the next and previous record, surroundig the current one.
	//
	// An order and direction must be given in query, use DESC direction for
	// date fields, so that next is newer and prev is older.
	//
	// FIXME This is a naive implemetation.
	// http://stackoverflow.com/questions/12293115/how-to-select-rows-surrounding-a-row-not-by-id
	public function neighbors($model, Behavior $behavior, Entity $entity, array $query = array()) {
		$query += [
			'conditions' => [],
			'fields' => [],
			'order' => [],
			// No other constraints can be used.
		];
		$next = $prev = null;

		if (!$query['order']) {
			throw new Exception('Need field/direction in order, none set.');
		}

		$results = static::find('all', [
			'conditions' => $query['conditions'],
			'order' => $query['order'],
			'fields' => ['id']
		])->to('array', ['indexed' => false]);

		foreach ($results as $key => $result) {
			if ($result['id'] != $entity->id) {
				continue;
			}
			if (isset($results[$key + 1])) {
				$prev = $results[$key + 1]['id'];
			}
			if (isset($results[$key - 1])) {
				$next = $results[$key - 1]['id'];
			}
			break;
		}

		return [
			'prev' => $prev ? static::find('first', [
				'conditions' => ['id' => $prev],
				'fields' => $query['fields']
			]) : false,
			'next' => $next ? static::find('first', [
				'conditions' => ['id' => $next],
				'fields' => $query['fields']
			]) : false,
		];
	}
}

?>