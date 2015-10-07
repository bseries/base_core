<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
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

namespace base_core\models;

class Sites extends \base_core\models\Base {

	protected $_meta = [
		'connection' => false
	];

	protected static $_data = [];

	public static function register($id, array $data = []) {
		$data += [
			'id' => $id,
			'fqdn' => null // unused
		];
		static::$_data[$id] = static::create($data);
	}

	public static function find($type, array $options = []) {
		if ($type === 'all') {
			return static::$_data;
		} elseif ($type === 'list') {
			return array_combine($keys = array_keys(static::$_data), $keys);
		}
	}
}

?>