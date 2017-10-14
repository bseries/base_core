<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\core;

trait RegisterableEnumeration {

	public static function enum() {
		$results = [];

		foreach (static::registry(true) as $name => $item) {
			if (is_array($item)) {
				if (!empty($item['title'])) {
					$results[$name] = $item['title'];
				} else {
					$results[$name] = $name;
				}
			} elseif (is_object($item)) {
				if (method_exists($item, 'title')) {
					$results[$name] = $item->title();
				} elseif (!empty($item->title)) {
					$results[$name] = $item->title;
				} else {
					$results[$name] = $name;
				}
			}
		}
		return $results;
	}
}

?>