<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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