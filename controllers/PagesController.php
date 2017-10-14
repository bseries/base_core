<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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