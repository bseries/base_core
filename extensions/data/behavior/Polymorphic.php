<?php
/**
 * Base Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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
use lithium\core\Libraries;
use lithium\data\Entity;
use lithium\net\http\Router;
use lithium\util\Collection;
use lithium\util\Inflector;

// Manages polymorphic relationships. One per model.
class Polymorphic extends \li3_behaviors\data\model\Behavior {

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		if (!$model::hasField('model')) {
			throw new Exception("The field `model` was not found on `{$model}`.");
		}
		if (!$model::hasField('foreign_key')) {
			throw new Exception("The field `foreign_key` was not found on `{$model}`.");
		}
		return parent::_config($model, $behavior, $config, $defaults);
	}

	protected static function _filters($model, Behavior $behavior) {
		$model::applyFilter('save', function($self, $params, $chain) {
			$entity = $params['entity'];
			$data =& $params['data'];

			if (isset($data['model'])) {
				$data['model'] = static::_normalizeModel($data['model']);
			}
			if ($entity->model) {
				$entity->model = static::_normalizeModel($entity->model);
			}
			return $chain->next($self, $params, $chain);
		});
		$model::applyFilter('find', function($self, $params, $chain) {
			$conditions =& $params['options']['conditions'];

			if (isset($conditions['model'])) {
				$conditions['model'] = static::_normalizeModel($conditions['model']);
			}
			return $chain->next($self, $params, $chain);
		});
	}

	public function poly($model, Behavior $behavior, Entity $entity) {
		$pModel = $entity->model;

		return $pModel::find('first', [
			'conditions' => [
				'id' => $entity->foreign_key,
			]
		]);
	}

	public function polyExists($model, Behavior $behavior, Entity $entity) {
		$pModel = $entity->model;

		return (boolean) $pModel::find('count', [
			'conditions' => ['id' => $entity->foreign_key]
		]);
	}

	public function polyUrl($model, Behavior $behavior, Entity $entity, $request, array $url = []) {
		if (!$entity->polyExists()) {
			return;
		}
		$parts = explode('\\', $entity->model);
		$library = array_shift($parts);
		$controller = array_pop($parts);

		$url += [
			'library' => $library,
			'controller' => $controller,
			'id' => $entity->foreign_key
		];
		if (Router::match($url, $request)) {
			return $url;
		}
	}

	public function polyType($model, Behavior $behavior, Entity $entity, $separator = '/') {
		list(, $type) = explode('\models\\', $entity->model);
		return str_replace('_', $separator, Inflector::underscore(Inflector::singularize($type)));
	}

// Normalizes model parameter to fully namespaced one.
	// idempotent
	protected static function _normalizeModel($model) {
		if (strpos($model, '\\') !== false) {
			return $model;
		}
		// Convert product-groups into ProductGroups.
		$model = ucfirst(Inflector::camelize($model));

		// Will have no leading backslash.
		$located = Libraries::locate('models', $model);

		return $located ?: $model;
	}
}

?>