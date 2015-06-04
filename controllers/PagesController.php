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

namespace base_core\controllers;

use base_core\extensions\cms\Widgets;

class PagesController extends \base_core\controllers\BaseController {

	public function admin_home() {
		$widgets = Widgets::read()->find(function($item) {
			return $item['group'] === 'dashboard';
		});
		return compact('widgets');
	}
}

?>