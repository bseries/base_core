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

namespace base_core\security;

use Exception;
use lithium\security\Auth;
use base_core\models\Users;

class Gate {

	protected static $_roles = [];

	public static function registerRole($name, array $rights = []) {
		static::$_roles[$name] = $rights;
	}

	public static function roles() {
		return static::$_roles;
	}

	public static function check($right, array $options = []) {
		$options += [
			'user' => true
		];
		$role = static::_user($options['user'], 'role');

		if (!isset(static::$_roles[$role])) {
			return false;
		}
		foreach ((array) $right as $r) {
			if (!in_array($r, static::$_roles[$role])) {
				return false;
			}
		}
		return true;
	}

	// Provide true for user to check current one.
	protected static function _user($user, $field = null) {
		if ($user === true) {
			$user = Auth::check('default');
		} elseif (is_object($user)) {
			$user = $user->data();
		} elseif (is_numeric($user)) {
			$user = Users::find('first', [
				'conditions' => ['id' => $user]
			])->data();
		} elseif (is_array($user)) {
			$user = $user;
		} else {
			throw new Exception('Invalid value for $user.');
		}

		if (!$field) {
			return $user;
		}
		if (!isset($user[$field])) {
			throw new Exception("No field `{$field}` on \$user.");
		}
		return $user[$field];
	}

	public static function owned($entity, array $options = []) {
		$options += [
			'user' => true
		];
		$id = static::_user($options['user'], 'id');

		return $entity->user_id == $id; // Entity might have numerics as strings.
	}
}

?>