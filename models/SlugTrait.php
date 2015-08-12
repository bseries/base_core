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

use lithium\util\Inflector;

// @deprecated
trait SlugTrait {

	public function slug($entity) {
		trigger_error('SlugTrait is deprecated in favor of Sluggable behavior.', E_USER_DEPRECATED);

		if (!$entity->title && !$entity->name) {
			return;
		}
		return strtolower(Inflector::slug($entity->title ?: $entity->name));
	}
}

?>