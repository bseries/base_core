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

namespace base_core\models;

use base_core\models\Users;

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
		return false;
	}
}

?>