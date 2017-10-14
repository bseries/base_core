<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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