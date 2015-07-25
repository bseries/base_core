<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\extensions\helper;

use lithium\core\Libraries;

class User extends \lithium\template\Helper {

	protected static $_financial = null;

	public function link($item) {
		if (static::$_financial === null) {
			static::$_financial = (boolean) Libraries::get('billing_core');
		}
		if (!$item) {
			return '-';
		}
		if (static::$_financial) {
			$title = $item->name . '/' . $item->number;
		} else {
			$title = $item->name;
		}
		return $this->_context->html->link($title, [
			'library' => 'base_core',
			'controller' => 'Users', 'action' => 'edit',
			'id' => $item->id,
			'admin' => true
		]);
	}
}

?>