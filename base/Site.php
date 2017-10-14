<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
		$fqdn = $this->_config['fqdn'];

		if ($www === 'keep') {
			return $fqdn;
		}
		if ($www === 'drop') {
			if (strpos($fqdn, 'www.') !== 0) {
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