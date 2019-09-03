<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use base_core\extensions\cms\Settings;
use base_core\base\Sites;
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

		$sites = null;
		if ($useSites = Settings::read('useSites')) {
			$sites = Sites::enum();
		}

		return compact('data', 'useOwner', 'useSites', 'sites') + $this->_selects();
	}
}

?>