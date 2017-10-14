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
			return Filters::run($model, 'statusChange', $params, function($params) {
				return $params['entity']->statusChange(
					$params['from'],
					$params['to']
				);
			});
		});
	}
}

?>