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
 * License. If not, see http://atelierdisko.de/licenses.
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