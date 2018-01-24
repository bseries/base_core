<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
		$options += [
			'user' => true
		];
		$role = static::user($options['user'], 'role');

		if (!$role) {
			return false;
		}
		if (!isset(static::$_roles[$role])) {
			throw new OutOfBoundsException("Unknown role `{$role}`.");
		}
		foreach ((array) $right as $r) {
			if (!isset(static::$_rights[$r])) {
				throw new OutOfBoundsException("Unknown or missing right `{$right}`.");
			}
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
		if (!array_key_exists($field, $user)) {
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