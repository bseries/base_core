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

use lithium\util\String;

class Uuid extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'uuid'
	];

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			if (isset($params['options']['whitelist'])) {
				$params['options']['whitelist'][] = $behavior->config('field');
			}
			$params['data'] = static::_uuid($behavior, $params['entity'], $params['data']);

			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _uuid($behavior, $entity, $data) {
		$field = $behavior->config('field');

		if (!$entity->exists() && $field) {
			$data[$field] = String::uuid();
		}
		return $data;
	}
}

?>