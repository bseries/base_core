<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\util;

use Exception;
use SplFixedArray;

class TopologicalSorter {

	protected $_data = [];

	public function add($item, array $dependencies = []) {
		if (!is_scalar($item)) {
			throw new Exception('Cannot use non-scalar item.');
		}
		$this->_data[$item] = $dependencies;
	}

	public function resolve() {
		//		$sorted  = new SplFixedArray(count($this->_data));
		$sorted = [];
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