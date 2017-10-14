<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use Exception;
use lithium\analysis\Logger;

trait TransferTrait {

	protected function _transfer() {
		Logger::write('debug', 'Handling transfer request.');

		if (!empty($this->request->data['form']['tmp_name'])) {
			if (!$stream = fopen($file = $this->request->data['form']['tmp_name'], 'r')) {
				throw new Exception("Failed to open temporary file `{$file}` in transfer.");
			}
		} else {
			if (!$stream = fopen('php://input', 'r')) {
				throw new Exception('Failed to open transfer stream for reading.');
			}
		}
		return $stream;
	}
}

?>