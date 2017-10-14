<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\helper;

class User extends \lithium\template\Helper {

	public function link($item) {
		if (!$item) {
			return '-';
		}
		return $this->_context->html->link($item->title(), [
			'library' => 'base_core',
			'controller' => 'Users', 'action' => 'edit',
			'id' => $item->id,
			'admin' => true
		]);
	}
}

?>