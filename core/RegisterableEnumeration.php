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
 * License. If not, see https://atelierdisko.de/licenses.
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