<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
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

class StatusChange extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'status'
	];

	protected static function _filters($model, Behavior $behavior) {
		if (!$model::respondsTo('statusChange')) {
			throw new Exception("No statusChange() method implemented in model `{$model}`.");
		}

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