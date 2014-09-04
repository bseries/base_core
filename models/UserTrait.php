<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_core\models;

use cms_core\models\Users;
use cms_core\models\VirtualUsers;

trait UserTrait {

	public function user($entity) {
		if ($entity->user_id) {
			return Users::find('first', [
				'conditions' => [
					'id' => $entity->user_id
				]
			]);
		}
		return VirtualUsers::find('first', [
			'conditions' => [
				'id' => $entity->virtual_user_id
			]
		]);
	}
}

?>