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
use li3_access\security\Access as SecurityAccess;

class Access extends \li3_behaviors\data\model\Behavior {

	public function hasAccess($model, Behavior $behavior, Entity $entity, $user) {
		return SecurityAccess::check('entity', $user, ['request' => $entity], [
			'rules' => $entity->access
		]) === [];
	}
}

?>