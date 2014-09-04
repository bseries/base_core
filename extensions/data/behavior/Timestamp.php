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

// use \Exception;

class Timestamp extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => ['created' => 'created', 'modified' => 'modified']
	];

	protected static function _filters($model, $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			if (isset($params['options']['whitelist'])) {
				foreach ($behavior->config('fields') as $field) {
					$params['options']['whitelist'][] = $field;
				}
			}
			$params['data'] = static::_timestamp($behavior, $params['entity'], $params['data']);

			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _timestamp($behavior, $entity, $data) {
		$now = date('Y-m-d H:i:s');
		$fields = $behavior->config('fields');

		if (!$entity->exists() && $fields['created']) {
			$data[$fields['created']] = $now;
		}
		if ($fields['modified']) {
			$data[$fields['modified']] = $now;
		}

		return $data;
	}
}

?>