<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\helper;

use base_core\models\Locales;

class G11n extends \lithium\template\Helper {

	public function name($locale) {
		return Locales::find('first', ['conditions' => ['id' => $locale]])->name;
	}
}

?>