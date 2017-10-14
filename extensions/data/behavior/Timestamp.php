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

// Adds timestamps to created and modified fields. Sets the created field once when the
// record is created, and updates the modified field whenever the record is saved.
//
// To disable timestamping on save, use the `'modified'` and/or `'created'` options:
// ```
// $product->save(['title' => 'foo'], ['modified' => false]};
// ```
//
// The behavior takes a single `'fields'` option that allows to remap field names, i.e.
// when the modified field is named `'changed'`. Or switch off the fields entirely:
//
// ```
// fields' => [
//    'created' => 'created',
//    'modified' => 'changed'
// ...
// fields' => [
//    'created' => 'created',
//    'modified' => false
// ```
class Timestamp extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'fields' => [
			'created' => 'created',
			'modified' => 'modified'
		]
	];

	protected static function _filters($model, Behavior $behavior) {
		Filters::apply($model, 'save', function($params, $next) use ($behavior) {
			$skip = [];

			foreach ($behavior->config('fields') as $name => $field) {
				if (isset($params['options'][$field])) {
					if (!$params['options'][$field]) {
						$skip[] = $name;
					}
					unset($params['options'][$field]);
				}
			}

			// Bypass whitelist, our fields hold meta data.
			if (isset($params['options']['whitelist'])) {
				foreach ($behavior->config('fields') as $name => $field) {
					if (!in_array($name, $skip)) {
						$params['options']['whitelist'][] = $field;
					}
				}
			}
			$params['data'] = static::_timestamp(
				$behavior, $params['entity'], (array) $params['data'], $skip
			);

			return $next($params);
		});
	}

	protected static function _timestamp(Behavior $behavior, Entity $entity, array $data, array $skip) {
		$now = date('Y-m-d H:i:s');
		$fields = $behavior->config('fields');

		if (!$entity->exists() && $fields['created'] && !in_array('created', $skip)) {
			$data[$fields['created']] = $now;
		}
		if ($fields['modified'] && !in_array('modified', $skip)) {
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