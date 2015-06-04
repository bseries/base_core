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

use lithium\util\Inflector;

trait SlugTrait {

	public function slug($entity) {
		if (!$entity->title && !$entity->name) {
			return;
		}
		return strtolower(Inflector::slug($entity->title ?: $entity->name));
	}
}

?>