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

namespace base_core\extensions\data\behavior;

use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;
use lithium\util\Inflector;

class Sluggable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'length' => 50
	];

	public function slug($model, Behavior $behavior, Entity $entity, $value = null) {
		if (!$value && !$entity->title && !$entity->name) {
			return;
		}
		$value = $value ?: ($entity->title ?: $entity->name);
		$slug = strtolower(Inflector::slug($slug));

		if (strlen($slug) > ($length = $behavior->config('length'))) {
			$slug = rtrim(substr($slug, 0, $length), '-');
		}
		return $slug;
	}
}

?>