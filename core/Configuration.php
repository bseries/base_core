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

namespace base_core\core;

class Configuration {

	protected $_data = [];

	/**
	 * Constructor.
	 *
	 * @param array|callable $config Either an array of configuration data or a callable
	 *        which will be used for lazy initialization-
	 * @return void
	 */
	public function __construct(array $config) {
		$this->_data = $config;
	}

	public function __get($name) {
		if (!array_key_exists($name, $this->_data)) {
			return null;
		}
		return $this->_data[$name];
	}

	public function __set($name, $value) {
		$this->_data[$name] = $value;
	}
}

?>