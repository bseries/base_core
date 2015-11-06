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

use Exception;
use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;

class Neigbors extends \li3_behaviors\data\model\Behavior {

	// Retrieves the next and previous record, surrounding the current one.
	//
	// An order and direction should be given in query, use DESC direction for
	// date fields, so that next is newer and prev is older.
	//
	// FIXME This is a naive implemetation. Better:
	//       http://stackoverflow.com/questions/12293115/how-to-select-rows-surrounding-a-row-not-by-id
	public function neighbors($model, Behavior $behavior, Entity $entity, array $query = array()) {
		$next = $prev = null;

		$results = $model::find('all', [
			'fields' => ['id']
		] + $query)->to('array', ['indexed' => false]);

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
			'prev' => $prev ? $model::find('first', $query) : false,
			'next' => $next ? $model::find('first', $query) : false
		];
	}
}

?>