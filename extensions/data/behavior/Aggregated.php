<?php
/**
 * Base Core
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
use lithium\util\Set;
use lithium\util\Collection;
use Exception;

/**
 * This behavior allows to combine results from multiple models into one
 * consistent "stream". This is accomplished by overriding the find model
 * method. The behavior of the original find method is tried to be matched
 * as closely as possible. Current finders that are available are `all`,
 * `first`, `count` as well as additionally `pages` (read more about
 * this later).
 *
 * There are - however - a couple of differences to normal model usage:
 *
 *   - The model this behavior is used with *must* be connection-less.
 *   - Models must register to the aggregation stream.
 *   - There is no `order` functionality. As a replacement use the
 *     `sorter` option and provide a custom compare function with it.
 *   - The data returned from the `all` finder will be encapsulated
 *     in a plain `Collection` object instead of a `RecordSet`.
 *
 * The find operations (especially with the all finder) are optimized
 * so that they query for as little results as possible. However
 * in case the registered models are all filled up well and regulary
 * exceed the `limit` provided directly through the limit option or
 * indirectly when using paging functionality, the  numer of items
 * being worked with will be O(number of registered models).
 *
 * On top of the aggregation functionality, paging functionality is
 * provided. Use it by providing the `page` option. How many items
 * are assigned to each page can be controlled via the `perPage`
 * option which defaults to 10 items per page. Pages are *not*
 * zero based.
 *
 * @link http://php.net/uasort
 */
class Aggregated extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		// name => fully namespaced
		'models' => []
	];

	public static function aggregate($model, Behavior $behavior, $type, array $options = []) {
		if (!isset($options['aggregate'])) {
			throw new Exception('The aggregate option is mandatory.');
		}
		if (isset($options['order'])) {
			throw new Exception('The order option is not supported, use sorter instead.');
		}

		if ($type == 'all') {
			$data = [];

			$options += [
				'page' => null,
				'perPage' => 10,
				'sorter' => null, // function() {}
				'limit' => null // overall aggreation limit
			];

			if ($options['page'] && $options['limit']) {
				throw new Exception('Page and limit options are mutually exclusive.');
			}

			foreach ($options['aggregate'] as $n => $o) {
				// We assume that in the worst case where only results
				// for one model will be returned after sorting and limiting,
				// that we'll need maximum of `limit`'ed items per model.
				//
				// We canot however calculate limits when paging as sorting
				// is applied later.
				$o['limit'] = $options['limit'];

				$_model = $behavior->config('models')[$n];

				foreach ($_model::find('all', $o) as $result) {
					// Prefix key with model to make it unique
					// and allow for quick lookup by index lookup.
					$data[$_model . ':' . $result->id] = $model::create([
						'original' => $result
					]);
				}
			}

			// We cannot use the sort method on a collection here as we
			// cannot use a collection object earlier as array_slice()
			// will refuse to operate on a collection object.
			if ($options['sorter']) {
				uasort($data, $options['sorter']);
			}

			if ($options['page']) {
				$data = array_slice(
					$data,
					$options['page'] > 1 ? $options['perPage'] * $options['page'] : 0,
					$options['perPage']
				);
			} elseif ($options['limit']) {
				$data = array_slice($data, 0, $options['limit']);
			}
			return new Collection(['data' => &$data]);

		} elseif ($type == 'first') {
			if (!is_array($options['aggregate'])) {
				throw new Exception('Aggregation option must be array of names.');
			}

			foreach (Set::normalize($options['aggregate']) as $n => $o) {
				$_model = $behavior->config('models')[$n];

				if ($result = $_model::find('first', (array) $o)) {
					return $model::create(['original' => $result]);
				}
			}
			return;

		} elseif ($type == 'count') {
			if (!is_array($options['aggregate'])) {
				throw new Exception('Aggregation option must be array of names.');
			}
			$result = 0;

			foreach ($options['aggregate'] as $name => $value) {
				$_model = $behavior->config('models')[$name];
				$result += $_model::find('count');
			}
			return $result;
		}
	}

	public function type($model, Behavior $behavior, Entity $entity) {
		foreach ($behavior->config('models') as $name => $_model) {
			if ($_model === $entity->original->model()) {
				return $name;
			}
		}
		return false;
	}
}

?>