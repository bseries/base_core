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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\extensions\cms;

use lithium\util\Set;
use lithium\net\http\Router;
use Exception;

// This class is used to generate the overall admin navigation bars.
// Panes may have subpanes but just one level deep. Actions of panes
// are not handled intentionally.
class Panes extends \lithium\core\StaticObject {

	protected static $_data = [];

	public static function register($name, array $options = []) {
		list($primary, $secondary) = explode('.', $name, 2) + [null, null];

		$options += [
			'title' => $name,
			'url' => null,
			'active' => null,
			// The more weight the lower the pane moves.
			// Should be a number between 0-100 inclusive.
			'weight' => 0,
			'panes' => $secondary ? false : []
		];
		if (is_callable($options['url'])) {
			$options['url'] = $options['url']();
		}
		if ($secondary) {
			static::$_data[$primary]['panes'][$secondary] = compact('name') + $options;
		} else {
			if (isset(static::$_data[$primary])) {
				static::$_data[$primary] += compact('name') + $options;
			} else {
				static::$_data[$primary] = compact('name') + $options;
			}
		}
	}

	// Only if $request is provided we can determine current active.
	public static function read($request) {
		$sorter = function($a, $b) {
			if ($a['weight'] === $b['weight']) {
				return 0;
			}
			return $a['weight'] > $b['weight'] ? 1 : -1;
		};

		// This will modify the registered data but maybe
		// save us some cycles if read again. Sorter then
		// doesn't have to do much sorting.
		uasort(static::$_data, $sorter);

		foreach (static::$_data as &$item) {
			// While we're here sort subpanes.
			uasort($item['panes'], $sorter);

			if (!$item['url']) {
				if ($item['panes']) {
					// Some primary panes are just predefined and may or may not have sub-panes
					// depending if modules register sub-panes for it.
					// continue;

					// Use first action url as url for group.
					// FIXME Move into register.
					$current = reset($item['panes']);
					$item['url'] = $current['url'];
				} else {
					continue;
				}
			}
			$results[] = $item;
		}

		$found = false;
		foreach ($results as &$pane) {
			if (!$pane['panes']) {
				continue;
			}

			if (($key = static::_active($pane['panes'], $request)) !== false) {
				// We can simplify things here as we don't need to also set the sub-panes active.
				$found = $pane['panes'][$key]['active'] = $pane['active'] = true;
				break;
			}
		}
		// For secondary panes and primary panes s a fallback i.e. dashboard.
		if (!$found) {
			if (($key = static::_active($results, $request)) !== false) {
				$results[$key]['active'] = true;
			}
		}
		return $results;
	}

	// Best (longest) match.
	// Each item in $data must have a url under the `url` key.
	protected static function _active($data, $request) {
		$map = [];
		foreach ($data as $key => $item) {
			if (!$item['url'] || (is_string($item['url']) && strpos($item['url'], 'http') !== false)) {
				// Already skip external urls here to make search set smaller.
				continue;
			}
			$map[$key] = Router::match($item['url'], $request, [
				// As we use scopes and those may force all URLs a absolutely routed,
				// we have to ensure non-absolute URLs so that matching below still works.
				'absolute' => false
			]);
		}
		uasort($map, function($a, $b) {
			// Sort by length, longest comes first.
			return strlen($b) - strlen($a);
		});
		foreach ($map as $key => $value) {
			// Request URL is the more detailed URL. Item URLs may be a subset of it.
			if (strpos($request->url, $value) !== false) {
				return $key;
			}
		}
		return false;
	}
}

?>