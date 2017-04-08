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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\extensions\command;

use lithium\core\Libraries;

class Mail extends \lithium\console\Command {

	/**
	 * Searches through all base/cms/billing/ecommerce modules
	 * for available mail templates.
	 */
	public function available() {
		foreach (glob(PROJECT_PATH . '/app/libraries/*/mails/*.php') as $file) {
			$library = basename(dirname(dirname($file)));
			$this->out(sprintf('%-30s via %s', basename($file), $library));
		}
	}
}

?>