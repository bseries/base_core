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