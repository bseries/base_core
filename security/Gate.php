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
use OutOfBoundsException;
use base_core\models\Users;
use li3_access\security\AccessDeniedException;
use lithium\security\Auth;

class Gate {

	protected static $_rights = [];

	protected static $_roles = [];

	public static function registerRight($name) {
		static::$_rights[$name] = true;
	}

	public static function registerRole($name, array $rights = []) {
		static::$_roles[$name] = $rights;
	}

	public static function rights() {
		return static::$_roles;
	}

	public static function roles() {
		return static::$_roles;
	}

	public static function checkRight($right, array $options = []) {
		if (!isset(static::$_rights[$right])) {
			throw new OutOfBoundsException("Unknown or missing right `{$right}`.");
		}
		$options += [
			'user' => true
		];
		$role = static::user($options['user'], 'role');

		if (!isset(static::$_roles[$role])) {
			throw new OutOfBoundsException("Unknown or missing role `{$role}`.");
		}
		foreach ((array) $right as $r) {
			if (!in_array($r, static::$_roles[$role])) {
				return false;
			}
		}
		return true;
	}

	// Provide true for user to check current one.
	public static function user($user, $field = null) {
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

		if (!$user) {
			return false;
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
			'user' => true,
			'require' => true
		];
		$id = static::user($options['user'], 'id');

		$result = $entity->owner_id == $id; // Entity might have numerics as strings.

		if ($options['require'] && !$result) {
			throw new AccessDeniedException();
		}
		return $result;
	}
}

?>