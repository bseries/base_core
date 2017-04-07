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

use li3_behaviors\data\model\Behavior;
use lithium\aop\Filters;
use lithium\data\Entity;
use lithium\util\String;

class Uuid extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'field' => 'uuid'
	];

	protected static function _filters($model, Behavior $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			if (isset($params['options']['whitelist'])) {
				$params['options']['whitelist'][] = $behavior->config('field');
			}
			if (!$params['entity']->exists()) {
				$params['data'] = static::_uuid($behavior, $params['data']);
			}
			return $next($params);
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