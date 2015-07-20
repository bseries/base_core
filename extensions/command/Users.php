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

namespace base_core\extensions\command;

use base_core\models\Users as UsersModel;
use base_core\models\VirtualUsers as VirtualUsersModel;
use lithium\util\String;

class Users extends \lithium\console\Command {

	public function create($name = null, $email = null, $password = null, $role = null) {
		$data['name'] = $name ?: $this->in('Name');
		$data['email'] = $email ?: $this->in('Email');
		$data['password'] = $cleartextPassword = $password ?: $this->in('Password');
		$data['role'] = $role ?: $this->in('Role');

		$data['password'] = UsersModel::hashPassword($data['password']);
		$data['is_active'] = true;

		$user = UsersModel::create($data);
		$result = $user->save(null, ['validate' => false]);

		if (!$result) {
			$this->out('Failed to create user!');
		} else {
			$this->out('Created user with:');
			$this->out('name: '. $data['name']);
			$this->out('email: '. $data['email']);
			$this->out('password: '. $cleartextPassword);
			$this->out('role: '. $data['role']);
		}
	}

	public function migrateUuid() {
		foreach (UsersModel::find('all') as $user) {
			$user->save([
				'uuid' => String::uuid()
			], ['validate' => false, 'whitelist' => ['id', 'uuid']]);
		}
		foreach (ViurtalUsersModel::find('all') as $user) {
			$user->save([
				'uuid' => String::uuid()
			], ['validate' => false, 'whitelist' => ['id', 'uuid']]);
		}
	}
}

?>