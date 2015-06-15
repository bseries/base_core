<?php
/**
 * Base
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\security\Auth;

trait AdminIndexOrderedTrait {

	public function admin_index() {
		$model = $this->_model;
		$user = Auth::check('default');

		$query = [
			'order' => ['order' => 'DESC']
		];

		if ($model::hasBehavior('Ownable') && $user['role'] !== 'admin') {
			$conditions['user_id'] = $user['id'];
		}

		$data = $model::find('all', $query);
		return compact('data') + $this->_selects();
	}
}

?>