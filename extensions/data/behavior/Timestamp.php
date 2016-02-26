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

use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Timestamp extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => ['created' => 'created', 'modified' => 'modified']
	];

	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			$skip = [];

			foreach ($behavior->config('fields') as $field) {
				if (isset($params['options'][$field])) {
					if (!$params['options'][$field]) {
						$skip[] = $field;
					}
					unset($params['options'][$field]);
				}
			}
			if (isset($params['options']['whitelist'])) {
				foreach ($behavior->config('fields') as $field) {
					if (!in_array($field, $skip)) {
						$params['options']['whitelist'][] = $field;
					}
				}
			}
			$params['data'] = static::_timestamp(
				$behavior, $params['entity'], (array) $params['data'], $skip
			);

			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _timestamp(Behavior $behavior, Entity $entity, array $data, array $skip) {
		$now = date('Y-m-d H:i:s');
		$fields = $behavior->config('fields');

		if (!$entity->exists() && $fields['created'] && !in_array($fields['created'], $skip)) {
			$data[$fields['created']] = $now;
		}
		if ($fields['modified'] && !in_array($fields['modified'], $skip)) {
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