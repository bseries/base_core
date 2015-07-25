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

// @deprecated
class User extends \li3_behaviors\data\model\Behavior {

	// @param $data array Can be used as an additional source to retrieve *_id from.
	//        Useful when data was passed to saved but not already saved.
	public function user($model, Behavior $behavior, Entity $entity, array $data = []) {
		trigger_error('The User behavior has been deprecated.', E_USER_DEPRECATED);

		$map = [
			'base_core\models\Users' => 'user_id'
		];
		foreach ($map as $model => $field) {
			if (!$entity->{$field} && !isset($data[$field])) {
				continue;
			}
			return $model::find('first', [
				'conditions' => [
					'id' => isset($data[$field]) ? $data[$field] : $entity->{$field}
				]
			]);
		}
		return false;
	}
}

?>