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

/**
 * A lazy load-able configuration.
 */
class Configuration {

	protected $_data = [];

	protected $_initializer = null;

	protected $_isInitialized = false;

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration for this class. The available options are as follows:
	 *        - `'initializer'` _null|callable_: A callable that will be used to initialize the
	 *          instance. Good for lazyily initializing objects for this configuration.
	 *        - `'data'` _array_: The actual configuration data.
	 * @return void
	 */
	public function __construct(array $config = []) {
		$config += [
			'initializer' => null,
			'data' => []
		];
		if (!$this->_initializer = $config['initializer']) {
			$this->_isinitialized = true;
		}
		$this->_data = $config['data'];
	}

	public function __get($name) {
		if (!array_key_exists($name, $this->_data)) {
			return null;
		}
		if ($this->_initialized) {
			return $this->_data[$offset];
		}
		$initializer = $this->_intializer;
		$this->_data = $initializer($this->_data);

		$this->_isInitialized = true;
		return $this->_data[$name];
	}

	public function __set($name, $value) {
		$this->_isInitialized = false;
		$this->_data[$name] = $value;
	}
}

?>