<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\command;

use base_core\models\Users as UsersModel;
use lithium\core\Libraries;

class Users extends \lithium\console\Command {

	public function initial() {
		$a = $this->in('Create initial administrator user?', [
			'choices' => ['y', 'n'],
			'default' => 'n'
		]);
		if ($a === 'y') {
			$this->_create([
				'name' => 'Administrator',
				'email' => 'infra@atelierdisko.de',
				'is_active' => true,
				'role' => 'admin',
				'password' => UsersModel::generatePassword(12, 2)
			]);
		}
		$a = $this->in('Create test users?', [
			'choices' => ['y', 'n'],
			'default' => 'n'
		]);
		if ($a === 'y') {
			$this->_create([
				'name' => 'Kate Miller',
				'email' => 'kate@atelierdisko.de',
				'is_active' => true,
				'role' => 'user',
				'password' => UsersModel::generatePassword(10, 0)
			]);
			$this->_create([
				'name' => 'John Smith',
				'email' => 'john@atelierdisko.de',
				'is_active' => true,
				'role' => 'client',
				'password' => UsersModel::generatePassword(10, 0)
			]);
		}
	}

	public function create($name = null, $email = null, $password = null, $role = null) {
		$data['name'] = $name ?: $this->in('Name');
		$data['email'] = $email ?: $this->in('Email');
		$data['password'] = $password ?: $this->in('Password', [
			'default' =>UsersModel::generatePassword(12, 1)
		]);
		$data['role'] = $role ?: $this->in('Role');
		$data['is_active'] = true;
		$this->_create($data);
	}

	protected function _create(array $data) {
		$cleartextPassword = null;

		if (isset($data['password'])) {
			$cleartextPassword = $data['password'];
			$data['password'] = UsersModel::hashPassword($data['password']);
		}
		$user = UsersModel::create($data);
		$result = $user->save(null, ['validate' => false]);

		if (!$result) {
			$this->out('Failed to create user!');
		} else {
			$this->out('Created user with:');
			$this->out('name     : ' . $data['name']);
			$this->out('email    : ' . $data['email']);
			$this->out('password : ' . $cleartextPassword);
			$this->out('role     : ' . $data['role']);
		}
	}
}

?>