<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use base_core\models\Users;

trait UsersTrait {

	protected function _users($item, array $options = []) {
		$options += [
			'field' => null, // user_id, id or owner_id
			'empty' => false
		];
		if ($options['empty']) {
			$users = [null => '-'];
		} else {
			$users = [];
		}
		if ($item->{$options['field']}) {
			$users += Users::find('list', [
				'conditions' => ['or' => ['is_active' => true, 'id' => $item->{$options['field']}]]
			]);
		} else {
			$users += Users::find('list', [
				'conditions' => ['is_active' => true]
			]);
		}
		return $users;
	}
}

?>