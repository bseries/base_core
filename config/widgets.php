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