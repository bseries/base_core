<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\adapter\security\auth;

use lithium\core\Libraries;
use lithium\core\ClassNotFoundException;

class Query extends \lithium\core\Object {

	protected $_model = 'Users';

	protected $_fields = array('id', 'token');

	protected $_scope = array();

	protected $_autoConfig = array('model', 'fields', 'scope');

	public function __construct(array $config = array()) {
		$defaults = array(
			'model' => 'Users',
			'fields' => array('id', 'token')
		);
		$config += $defaults;

		parent::__construct($config + $defaults);
	}

	protected function _init() {
		parent::_init();

		if (!class_exists($model = Libraries::locate('models', $this->_model))) {
			throw new ClassNotFoundException("Model class '{$this->_model}' not found.");
		}
		$this->_model = $model;
	}

	public function check($request, array $options = array()) {
		$model = $this->_model;
		$data = $request->query;

		$conditions = $this->_scope;

		foreach ($this->_fields as $field) {
			if (empty($data[$field])) {
				return false;
			}
			$conditions[$field] = $data[$field];
		}
		if (!$result = $model::find('first', compact('conditions') + $options)) {
			return false;
		}
		return $result->data();
	}

	public function set($data, array $options = array()) {
		return $data;
	}

	public function clear(array $options = array()) {}
}

?>