<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\data\behavior;

use li3_behaviors\data\model\Behavior;
use lithium\aop\Filters;
use lithium\data\Entity;
use lithium\util\Text;

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
			$data[$field] = Text::uuid();
		}
		return $data;
	}
}

?>