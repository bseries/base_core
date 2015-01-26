<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

trait AdminIndexOrderedTrait {

	public function admin_index() {
		$model = $this->_model;

		$data = $model::find('all', [
			'order' => ['order' => 'DESC']
		]);
		return compact('data') + $this->_selects();
	}
}

?>