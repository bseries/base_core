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

use lithium\data\Entity;
use li3_behaviors\data\model\Behavior;
use lithium\util\Inflector;

class Sluggable extends \li3_behaviors\data\model\Behavior {

	public function slug($model, Behavior $behavior, Entity $entity) {
		if (!$entity->title && !$entity->name) {
			return;
		}
		return strtolower(Inflector::slug($entity->title ?: $entity->name));
	}
}

?>