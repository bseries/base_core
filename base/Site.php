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

namespace base_core\base;

use BadMethodCallException;
use InvalidArgumentException;

class Site {

	protected $_config = [];

	public function __construct(array $config) {
		$this->_config = $config + [
			'title' => null,
			'fqdn' => null
		];
	}

	public function fqdn($www = 'keep') {
		$fqdn = $this->_config['fqnd'];

		if ($www === 'keep') {
			return $fqdn;
		}
		if ($www === 'drop') {
			if (strpos($fqnd, 'www.') !== 0) {
				return $fqdn;
			}
			return substr($fqdn, 4);
		}
		throw new InvalidArgumentException('Invalid value for $www: ' . $www);
	}

	public function __call($name, array $arguments) {
		if (!array_key_exists($name, $this->_config)) {
			throw new BadMethodCallException("Method or configuration `{$name}` does not exist.");
		}
		return $this->_config[$name];
	}
}

?>