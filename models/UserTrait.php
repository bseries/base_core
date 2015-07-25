<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\models;

use base_core\models\Users;
use base_core\models\VirtualUsers;

// @deprecated
trait UserTrait {

	public function user($entity) {
		trigger_error('UserTrait is deprecated in favor of Ownable behavior.', E_USER_DEPRECATED);

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