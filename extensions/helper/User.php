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