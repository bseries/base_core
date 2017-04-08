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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\extensions\data\behavior;

use Exception;
use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;
use li3_access\security\Access as SecurityAccess;

// TODO Check if access field is searialized!
class Access extends \li3_behaviors\data\model\Behavior {

	// $user is an array or null
	public function hasAccess($model, Behavior $behavior, Entity $entity, $user) {
		return SecurityAccess::check(
			'entity',
			$user,
			$entity,
			(array) $entity->access
		);
	}
}

?>