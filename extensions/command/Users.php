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

namespace base_core\extensions\command;

use base_core\models\Users as UsersModel;
use lithium\util\String;
use lithium\core\Libraries;

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

	// @deprecated
	public function migrateUuid() {
		$this->out('Migrating users to uuid...');

		foreach (UsersModel::find('all') as $user) {
			if ($user->uuid) {
				continue;
			}
			$user->save([
				'uuid' => String::uuid()
			], ['validate' => false, 'whitelist' => ['id', 'uuid']]);
		}
	}

	// @deprecated
	public function migrate13to14() {
		$this->migrateUuid();
		try {
			foreach (VirtualUsers::find('all') as $user) {
				if ($user->uuid) {
					continue;
				}
				$user->save([
					'uuid' => String::uuid()
				], ['validate' => false, 'whitelist' => ['id', 'uuid']]);
			}
		} catch (\Exception $e) {
			$this->out('Skipping virtual users! -> ' . $e->getMessage());
		}

		$this->out('Migrating virtual users to locked users...');

		try {
			foreach (VirtualUsers::find('all') as $v) {
				$u = UsersModel::create([
					'is_locked' => true
				] + array_diff_key($v->data(), [
					'id' => null
				]));

				if (!$u->save(null, ['validate' => false])) {
					$this->error('Failed to save new user.');
					continue;
				}
				$this->out('Mapped virtual user ' . $v->id . ' -> user ' . $u->id);

				if (!$this->_remapVirtual($v->id, $u->id)) {
					$this->error('Failed remapping.');
					continue;
				}
				$v->delete();
			}
		} catch (\Exception $e) {
			$this->out('Skipping virtual users! -> ' . $e->getMessage());
		}
		$this->out('You must now:');
		$this->out('- Apply the reset of the db migration.');
	}

	protected function _remapVirtual($old, $new) {
		$models = Libraries::locate('models');
		$results = [];

		foreach ($models as $model) {
			if (!$model::hasField('virtual_user_id')) {
				continue;
			}
			$results[] = $model;
		}
		$models = $results;

		foreach ($models as $model) {
			$results = $model::find('all', [
				'conditions' => ['virtual_user_id' => $old]
			]);
			foreach ($results as $result) {
				$r = $result->save([
					'user_id' => $new,
					'virtual_user_id' => null
				], ['whitelist' => ['id', 'user_id', 'virtual_user_id']]);

				if (!$r) {
					return false;
				}
			}
			return true;
		}
	}
}

// @deprecated
class VirtualUsers extends \base_core\models\Base {}

?>