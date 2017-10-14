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
use li3_behaviors\data\model\Behavior;
use lithium\data\Entity;
use lithium\util\Inflector;

class Sluggable extends \li3_behaviors\data\model\Behavior {

	protected static $_defaults = [
		'length' => 50
	];

	public function slug($model, Behavior $behavior, Entity $entity, $value = null) {
		if (!$value) {
			if (!$field = $model::meta('title')) {
				throw new Exception("No manual slug value and no title field.");
			} else {
				$value = $entity->{$field};
			}
		}
		if (!$value) {
			return null;
		}
		// Lowercase before transliteration, otherwise transliteration
		// may see partial lowercase characters (Ä -> Ae) and interpretes
		// them as new words, adding spaces mid-word.
		if (function_exists('mb_strtolower')) {
			$slug = Inflector::slug(mb_strtolower($value));
		} else {
			$slug = Inflector::slug(strtolower($value));
		}

		if (strlen($slug) > ($length = $behavior->config('length'))) {
			$slug = rtrim(substr($slug, 0, $length), '-');
		}
		return $slug;
	}
}

?>