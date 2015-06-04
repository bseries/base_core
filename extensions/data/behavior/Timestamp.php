<?php
/**
 * Base
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\data\behavior;

use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Timestamp extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => ['created' => 'created', 'modified' => 'modified']
	];

	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			if (isset($params['options']['whitelist'])) {
				foreach ($behavior->config('fields') as $field) {
					$params['options']['whitelist'][] = $field;
				}
			}
			$params['data'] = static::_timestamp(
				$behavior, $params['entity'], (array) $params['data']
			);

			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _timestamp(Behavior $behavior, Entity $entity, array $data) {
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

	public static function touchTimestamp($model, Behavior $behavior, $id, $field) {
		return $model::update(
			[$field => date('Y-m-d H:i:s')],
			['id' => $id]
		);
	}
}

?>