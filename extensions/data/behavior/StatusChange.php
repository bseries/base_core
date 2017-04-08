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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use li3_behaviors\data\model\Behavior;
use lithium\aop\Filters;

/**
 * Detects changes to the status field and executes corresponding method on model. The
 * method will be called after the field has been persisted.
 *
 * This behavior abstracts details about modification of the field away.
 *
 * In future versions a state machine can be used to define possible transitions between
 * statuses.
 */
class StatusChange extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'status'
	];

	protected static function _filters($model, Behavior $behavior) {
		if (!$model::respondsTo('statusChange')) {
			throw new Exception("No statusChange() method implemented in model `{$model}`.");
		}

		Filters::apply($model, 'save', function($params, $next) use ($model, $behavior) {
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
				return $next($params);
			}
			$old = null;

			if ($params['entity']->exists()) {
				// FIXME modified method does not work, why?
				$old = $model::find('first', [
					'conditions' => ['id' => $params['entity']->id],
					'fields' => [$field]
				]);
				if ($old->$field == $to) {
					return $next($params);
				}
			}
			if (!$result = $next($params)) {
				return false;
			}

			$params = compact('to') + [
				'from' => $old ? $old->$field : null,
				'entity' => $params['entity']
			];
			return $model::invokeMethod('_filter', ['statusChange', $params, function($self, $params) {
				return $params['entity']->statusChange(
					$params['from'],
					$params['to']
				);
			}]);
		});
	}
}

?>