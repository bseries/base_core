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

use base_core\security\Gate;

trait AdminIndexOrderedTrait {

	public function admin_index() {
		$model = $this->_model;

		$query = [
			'order' => ['order' => 'DESC']
		];

		if ($model::hasBehavior('Ownable') && !Gate::check('users') && !Gate::owned($item)) {
			$conditions['owner_id'] = $user['id'];
		}

		$data = $model::find('all', $query);
		$useOwner = Gate::check('users');

		return compact('data', 'useOwner') + $this->_selects();
	}
}

?>