<?php
/**
 * Base Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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