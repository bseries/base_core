<?php
/**
 * Bureau
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_core\extensions\data\behavior;

use Exception;
use lithium\util\Set;

/**
 * Allows for storing a sequential weight value with your entities. And
 * manipulating these if required.
 *
 * By default - when order by weight descending - new entities will
 * receive the highest weight possible and thus be placed at the very
 * beginning of the order.
 *
 * @fixme Currently is tied to sources using database adapters.
 */
class Sortable extends \li3_behaviors\data\model\Behavior {

	/**
	 * Default options.
	 *
	 * `'descend'`: When `true` a highest weight indicates top order position, use it
	 *              when sorting by weight descending and want new entities to be
	 *              inserted at the very top.
	 *              When `false` a highest weight indicates bottom order position, use
	 *              it when sorting by weight asceding and want new entities to
	 *              be inserted at the very bottom.
	 */
	protected $_defaults = [
		'field' => 'order',
		'cluster' => [],
		'descend' => true
	];

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($model, $behavior) {
			if (!$params['entity']->exists()) {
				$cluster = static::_cluster(
					$model, $behavior,
					null, false, $params['data']
				);
				$params['data'][$config['field']] = static::_highestWeight(
					$model, $behavior, $cluster
				) + 1;
			}
			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _highestWeight($model, $behavior, $cluster = []) {
		$field = $behavior->config('field');
		$fieldEscaped = $model::connection()->name($field);

		$result = $model::find('first', array(
			'conditions' => $cluster,
			'fields' => 'MAX(' . $fieldEscaped . ') AS `highest_weight`',
		));
		return current($result->data());
	}

	/**
	 * Updates the weights giving a sequence of ids. Allows for sparse
	 * set. The order of the ids in the sequence dictates the final
	 * order of the corresponding entities _relative_ to each other.
	 *
	 * When `descend` is true, first ID will get the highest weight,
	 * else first ID will get lowest weight.
	 *
	 * Uses transactions automatically for isolation.
	 *
	 * Flow (ID/ORDER:
	 * 1 - 1                 1 - 1      2 - 2     2 - 1
	 * 2 - 2  [1 below 2]--> 2 - 2  --> 1 - 3 --> 1 - 2
	 * 3 - 3                 3 - 4      3 - 4     3 - 3
	 *
	 * @param array $ids
	 * @return boolean
	 */
	public static function weightSequence($model, $behavior, $ids) {
		if ($behavior->config('descend')) {
			$ids = array_reverse($ids);
		}
		$connection = $model::connection()->connection;

		$connection->beginTransaction();
		$cluster = static::_cluster($model, $behavior, $previous = array_shift($ids));
		foreach ($ids as $id) {
			if (!static::_moveBelow($model, $behavior, $id, $previous, $cluster)) {
				$connection->rollback();
				return false;
			}
			$previous = $id;
		}
		$connection->commit();
		return true;
	}

	/**
	 * Retrieves missing cluster values, good for using in conditions when updating order.
	 *
	 * @param mixed $id ID of the entity to find exemplaric cluster values for. May be `null`
	 *              if $data already contains all cluster values.
	 * @param boolean $forceFind Forces all cluster values to be retrieved even if contained
	 *                already in $data.
	 * @param array $data Data that already contains (parts of) cluster fields.
	 * @return array Cluster fields with values for $id usable as conditions.
	 */
	protected static function _cluster($model, $behavior, $id = null, $forceFind = false, array $data = []) {
		$missing = [];
		$conditions = [];

		foreach ($behavior->config('cluster') as $field) {
			if (empty($data[$field]) || $forceFind) {
				$missing[] = $field;
			} else {
				$conditions[$field] = $data[$field];
			}
		}
		if ($missing) {
			if (!$id) {
				throw new Exception('Could not determine cluster values.');
			}
			$result = $model::find('first', array(
				'conditions' => compact('id'),
				'fields' => $missing,
				'order' => false
			));
			if (!$result) {
				throw new Exception('Could not determine cluster values.');
			}
			$conditions += $result->data();
			unset($conditions['id']);
		}
		return $conditions;
	}

	/**
	 * Moves an entity below another. Checks if action is required at all first.
	 * Opens a gap, sets weight of entity, filling the gap, then ensuring the gap
	 * is closed correctly.
	 */
	protected static function _moveBelow($model, $behavior, $id, $belowId, $cluster = []) {
		$weights = [];
		$results = $model::find('all', array(
			'conditions' => array('id' => array($id, $belowId)),
			'fields' => array('id', $field = $behavior->config('field')),
			'order' => false
		));
		foreach ($results as $result) {
			$weights[$result->id] = $result->{$field};
		}

		if (count($weights) != 2) {
			throw new Exception("Could not retrieve weights for id `{$id}` and ``{$belowId}`");
		}

		if ($weights[$id] > $weights[$belowId]) {
			return true;
		}

		if (!static::_openGap($model, $behavior, $weights[$belowId], $cluster)) {
			return false;
		}
		$result = $model::update(
			[$field => $weights[$belowId] + 1],
			compact('id')
		);
		if (!$result) {
			return false;
		}
		return static::_closeGap($model, $behavior, $weights[$id], $cluster);
	}

	protected static function _openGap($model, $behavior, $atWeight, $cluster = []) {
		$field = $behavior->config('field');
		$fieldEscaped = $model::connection()->name($field);

		/*
		$result = $model::update(
		 array($field => $fieldEscaped . ' + 1')  + $cluster ,
		 array($fieldEscaped . ' >= ' . $atWeight)
		 );
		*/
		$connection = $model::connection()->connection;
		$table = $model::meta('source');

		$sql  =  "UPDATE `$table` SET {$fieldEscaped} = ($fieldEscaped + 1)";
		$sql .= " WHERE {$fieldEscaped} > $atWeight";
		$result = $connection->query($sql);
		$result = $result->errorCode() == '00000';

		if (!$result) {
			throw new Exception("Failed to create gap below id `{$belowId}`");
		}
		return $result;
	}

	protected static function _closeGap($model, $behavior, $atWeight, $cluster = []) {
		$field = $behavior->config('field');
		$fieldEscaped = $model::connection()->name($field);

		/*
		return $model::update(
			array($field => $fieldEscaped . ' - 1'),
			array($fieldEscaped . ' >' => $atWeight) + $cluster
		);
		*/
		$connection = $model::connection()->connection;
		$table = $model::meta('source');

		$sql  =  "UPDATE `$table` SET {$fieldEscaped} = ($fieldEscaped - 1)";
		$sql .= " WHERE {$fieldEscaped} > $atWeight";
		$result = $connection->query($sql);
		return $result->errorCode() == '00000';
	}
}

?>