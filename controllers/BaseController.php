<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use lithium\util\Inflector;

class BaseController extends \lithium\action\Controller {

	/**
	 * Fully namespaced name of the model that can
	 * be associated mainly with the controller.
	 *
	 * - Redefine in your controller, to prevent that
	 *   this is set automatically. -
	 *
	 * @var string
	 */
	protected $_model;

	/**
	 * Name of the library the controller belongs to.
	 *
	 * @var string
	 */
	protected $_library;

	/**
	 * Initializes parent, then populates more properties.
	 */
	protected function _init() {
		parent::_init();

		$class = explode('\\', get_called_class());

		if (!$this->_model) {
			$this->_model  = reset($class) . '\models\\';
			$this->_model .= Inflector::pluralize(str_replace('Controller', '', end($class)));
		}
		$this->_library = reset($class);
	}

	/**
	 * Populates select data.
	 */
	protected function _selects($item = null) {
		return [];
	}
}

?>