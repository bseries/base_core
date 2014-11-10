<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\data\behavior;

class StatusChange extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'status'
	];

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($model, $behavior) {
			$field = $behavior->config('field');
			$to = null;

			if ($params['entity']->exists()) {
				if (!empty($params['data'][$field])) {
					$to = $params['data'][$field];
				}
			} else {
				if (!empty($params['entity']->{$field})) {
					$to = $params['entity']->{$field};
				}
			}

			if (!$to) {
				return $chain->next($self, $params, $chain);
			}
			$old = null;

			if ($params['entity']->exists()) {
				// FIXME modified method does not work, why?
				$old = $model::find('first', [
					'conditions' => ['id' => $params['entity']->id],
					'fields' => [$field]
				]);
				if ($old->$field == $to) {
					return $chain->next($self, $params, $chain);
				}
			}
			if (!$result = $chain->next($self, $params, $chain)) {
				return false;
			}
			return $params['entity']->statusChange(
				$old ? $old->$field : null,
				$to
			);
		});
	}
}

?>