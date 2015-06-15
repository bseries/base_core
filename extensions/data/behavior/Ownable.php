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

class Ownable extends \li3_behaviors\data\model\Behavior {

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

	// $user can be either an instance of Entity or an array containing the `'id'` field or
	// just the id.
	public function isOwner($model, Behavior $behavior, Entity $entity, $user) {
		$id = null;

		if ($user instanceof Entity) {
			$id = $user->id;
		} elseif (is_array($user)) {
			$id = $user['id'];
		} elseif (is_numeric($user)) {
			$id = $user;
		} else {
			throw new Exception('Invalid value for $user.');
		}
		if (!$id) {
			throw new Exception('Could not extract user ID for owner check.');
		}
		return $entity->user_id == $id; // Entity might have numerics as strings.
	}
}

?>