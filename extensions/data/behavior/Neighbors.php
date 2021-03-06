<?php
/**
 * Copyright 2014 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;

class Neighbors extends \li3_behaviors\data\model\Behavior {

	// Retrieves the next and previous record, surrounding the current one.
	//
	// An order and direction should be given in query, use DESC direction for
	// date fields, so that next is newer and prev is older.
	//
	// FIXME This is a naive implemetation. Better:
	//       http://stackoverflow.com/questions/12293115/how-to-select-rows-surrounding-a-row-not-by-id
	public function neighbors($model, Behavior $behavior, Entity $entity, array $query = array()) {
		$next = $prev = null;

		// Do not rely on 'indexed' => false feature as we may get a regular collection as
		// a result. In general this should not be the case, but `Aggregated` behavior does.
		$results = array_values($model::find('all', [
			'fields' => ['id']
		] + $query)->to('array', ['indexed' => false]));

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
			'prev' => $prev ? $model::find('first', [
				'conditions' => ['id' => $prev]
			] + $query) : false,
			'next' => $next ? $model::find('first', [
				'conditions' => ['id' => $next]
			] + $query) : false
		];
	}
}

?>