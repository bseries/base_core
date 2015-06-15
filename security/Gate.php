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

use lithium\security\Auth;

class Gate {

	protected static $_roles = [];

	public static function registerRole($name, array $rights = []) {
		static::$_roles[$name] = $rights;
	}

	public static function roles() {
		return static::$_roles;
	}

	// Provide true for user to check current one.
	public static function check($right, array $options = []) {
		$options += [
			'user' => true,
			'require' => false
		];
		if ($options['user'] === true) {
			$user = Auth::check('default');
		} else {
			$user = $options['user'];
		}

		if (is_object($user)) {
			$role = $user->role;
		} elseif (is_array($user)) {
			$role = $user['role'];
		} else {
			return false;
		}

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

}

?>