<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\models;

class StatefulDocument implements \Finite\StatefulInterface {

	protected $_state;

	public function __construct($state) {
		$this->_state = (string) $state;
	}

	public function getFiniteState() {
		return $this->_state;
	}

	public function setFiniteState($state) {
		$this->_state = $state;
	}
}

?>