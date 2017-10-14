<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config;

use lithium\util\Inflector;
use lithium\g11n\Message;
use base_core\security\Gate;
use base_core\extensions\cms\Widgets;
use base_core\models\Users;

extract(Message::aliases());

if (Gate::checkRight('users')) {
	Widgets::register('users', function() use ($t) {
		$roles = Users::enum('role');

		foreach ($roles as $role) {
			$data[Inflector::pluralize($role)] = Users::find('count', [
				'conditions' => [
					'role' => $role
				]
			]);
		}

		return [
			'title' => $t('Users', ['scope' => 'base_core']),
			'url' => [
				'library' => 'base_core',
				'controller' => 'Users', 'action' => 'index',
				'admin' => true
			],
			'data' => array_filter($data)
		];
	}, [
		'type' => Widgets::TYPE_COUNTER,
		'group' => Widgets::GROUP_DASHBOARD,
	]);
}

?>