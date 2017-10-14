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
use base_core\models\Users;

class Ownable extends \li3_behaviors\data\model\Behavior {

	protected static function _config($model, Behavior $behavior, array $config, array $defaults) {
		if (!$model::hasField('owner_id')) {
			throw new Exception("The field `owner_id` was not found on `{$model}`.");
		}
		return parent::_config($model, $behavior, $config, $defaults);
	}

	public function owner($model, Behavior $behavior, Entity $entity) {
		return Users::find('first', [
			'conditions' => [
				'id' => $entity->owner_id
			]
		]);
	}
}

?>