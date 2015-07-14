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

class Ownable extends \li3_behaviors\data\model\Behavior {

	public function owner($model, Behavior $behavior, Entity $entity) {
		return Users::find('first', [
			'conditions' => [
				'id' => $entity->owner_id
			]
		]);
	}
}

?>