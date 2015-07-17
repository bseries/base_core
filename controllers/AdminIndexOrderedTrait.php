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

use base_core\extensions\cms\Settings;
use base_core\security\Gate;

trait AdminIndexOrderedTrait {

	public function admin_index() {
		$model = $this->_model;

		$query = [
			'order' => ['order' => 'DESC']
		];

		if ($model::hasBehavior('Ownable')) {
			if (Settings::read('security.checkOwner') && !Gate::checkRight('owner')) {
				$conditions['owner_id'] = Gate::user(true, 'id');
			}
		}

		$data = $model::find('all', $query);
		$useOwner = Settings::read('security.checkOwner') && Gate::checkRight('owner');

		return compact('data', 'useOwner') + $this->_selects();
	}
}

?>