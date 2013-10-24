<?php

namespace cms_core\extensions\command;

use cms_core\models\Users as UsersModel;

class Users extends \lithium\console\Command {

	public function create($name = null, $email = null, $password = null, $role = null) {
		$data['name'] = $name ?: $this->in('Name');
		$data['email'] = $email ?: $this->in('Email');
		$data['password'] = $cleartextPassword = $password ?: $this->in('Password');
		$data['role'] = $role ?: $this->in('Role');


		$data['password'] = UsersModel::hashPassword($data['password']);

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
}

?>