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

namespace base_core\util;

use Exception;

class TopologicalSorter {

	protected $_data = [];

	public function add($item, array $dependencies = []) {
		if (!is_scalar($item)) {
			throw new Exception('Cannot use non-scalar item.');
		}
		$this->_data[$item] = $dependencies;
	}

	public function resolve() {
		$sorted  = new SplFixedArray(count($this->_data));
		$visited = [];

		foreach ($this->_data as $item => $_) {
			$this->_visit($item, $visited, $sorted);
		}
		return $sorted;
	}

	protected function _visit($item, &$visited, &$sorted) {
		if (isset($visited[$item])) {
			return;
		}
		$visited[$item] = true;

		foreach ($this->_data[$item] as $dependency) {
			if (!isset($this->_data[$dependency])) {
				throw new Exception("Dependency on unknown item `{$dependency}`.");
			}
			$this->_visit($dependency, $visited, $sorted);
		}
		$sorted[] = $item;
	}
}

?>