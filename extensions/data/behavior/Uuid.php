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

use lithium\util\String;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Uuid extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'uuid'
	];

	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			if (isset($params['options']['whitelist'])) {
				$params['options']['whitelist'][] = $behavior->config('field');
			}
			if (!$params['entity']->exists()) {
				$params['data'] = static::_uuid($behavior, $params['data']);
			}

			return $chain->next($self, $params, $chain);
		});
	}

	protected static function _uuid(Behavior $behavior, array $data) {
		if ($field = $behavior->config('field')) {
			$data[$field] = String::uuid();
		}
		return $data;
	}
}

?>