<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
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

namespace base_core\controllers;

use base_core\extensions\cms\Settings;
use base_core\models\Sites;
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

		if ($useSites = Settings::read('useSites')) {
			$sites = Sites::find('list');
		}

		return compact('data', 'useOwner', 'useSites', 'sites') + $this->_selects();
	}
}

?>