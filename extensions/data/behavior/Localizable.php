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

use Exception;
use NumberFormatter;
use lithium\core\Environment;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;

class Localizable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => [],
	];

	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) use ($behavior) {
			$params['options'] += ['localize' => true];

			$data = (array) $params['data'] + $params['entity']->data();

			if (!$params['options']['localize']) {
				return $chain->next($self, $params, $chain);
			}
			foreach ($behavior->config('fields') as $field => $type) {
				if (!isset($data[$field])) {
					continue;
				}
				$params['data'][$field] = static::_normalize($data[$field], $type);
			}
			return $chain->next($self, $params, $chain);
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