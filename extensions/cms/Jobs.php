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

namespace base_core\extensions\cms;

class Jobs extends \base_core\async\Jobs {

	public static function recur($name, $run, array $options = []) {
		trigger_error('Jobs moved to base_core\async\Jobs.', E_USER_DEPRECATED);
		return parent::recur($name, $run, $options);
	}

	public static function runName($name) {
		trigger_error('Jobs moved to base_core\async\Jobs.', E_USER_DEPRECATED);
		return parent::runName($name);
	}

	public static function read($name = null) {
		trigger_error('Jobs moved to base_core\async\Jobs.', E_USER_DEPRECATED);
		return parent::read($name);
	}

	public static function runFrequency($frequency) {
		trigger_error('Jobs moved to base_core\async\Jobs.', E_USER_DEPRECATED);
		return parent::runFrequency($frequency);
	}
}

?>