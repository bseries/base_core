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

namespace base_core\extensions\data\behavior;

use Exception;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;
use base_core\models\Users;
use base_core\models\VirtualUsers;

class User extends \li3_behaviors\data\model\Behavior {

	public function user($model, Behavior $behavior, Entity $entity) {
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