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
use NumberFormatter;
use li3_behaviors\data\model\Behavior;
use lithium\aop\Filters;
use lithium\core\Environment;
use lithium\data\Entity;

class Localizable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => [],
	];

	protected static function _filters($model, Behavior $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			$params['options'] += ['localize' => true];

			$data = (array) $params['data'] + $params['entity']->data();

			if (!$params['options']['localize']) {
				return $next($params);
			}
			foreach ($behavior->config('fields') as $field => $type) {
				if (!isset($data[$field])) {
					continue;
				}
				$params['data'][$field] = static::_normalize($data[$field], $type);
			}
			return $next($params);
		});
	}

	protected static function _normalize($value, $type) {
		switch ($type) {
			case 'number':
			case 'decimal':
				$formatter = new NumberFormatter(static::_locale(), NumberFormatter::DECIMAL);
				return $value = $formatter->parse($value);
			case 'money':
				$formatter = new NumberFormatter(static::_locale(), NumberFormatter::DECIMAL);
				$result = ($formatter->parse($value) * 100);
				return intval($result . '.0'); // Prevent float to int rounding issues.
		}
		throw new Exception('Field value is in unsupported format.');
	}

	protected static function _locale() {
		return Environment::get('locale');
	}

	protected static function _normalizeLocale() {
		return 'en_US';
	}
}

?>