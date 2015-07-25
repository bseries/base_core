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

namespace base_core\config;

use lithium\g11n\Message;
use base_core\security\Gate;
use base_core\extensions\cms\Widgets;
use base_core\models\Users;

extract(Message::aliases());

Widgets::register('support', function() use ($t) {
	return [
		'title' => $t('Contact Support', ['scope' => 'base_core']),
		'url' => 'http://atelierdisko.de/contact'
	];
}, [
	'type' => Widgets::TYPE_QUICKDIAL,
	'group' => Widgets::GROUP_DASHBOARD,
	'weight' => Widgets::WEIGHT_HIGH
]);

if (Gate::checkRight('users')) {
	Widgets::register('users', function() use ($t) {
		return [
			'title' => $t('Users', ['scope' => 'base_core']),
			'url' => [
				'library' => 'base_core',
				'controller' => 'Users', 'action' => 'index',
				'admin' => true
			],
			'data' => [
				$t('Total', ['scope' => 'base_core']) => Users::find('count')
			]
		];
	}, [
		'type' => Widgets::TYPE_COUNTER,
		'group' => Widgets::GROUP_DASHBOARD,
	]);
}

?>