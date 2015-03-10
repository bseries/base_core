<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\g11n\Message;
use base_core\extensions\cms\Widgets;
use base_core\models\Users;
use base_core\models\VirtualUsers;

$t = function($message, array $options = []) {
	return Message::translate($id, $options + ['scope' => 'base_core', 'default' => $message]);
};

Widgets::register('support', function() use ($t) {
	return [
		'title' => $t('Contact Support'),
		'url' => [
			'controller' => 'Pages', 'action' => 'support',
			'library' => 'base_core', 'admin' => true
		]
	];
}, [
	'type' => Widgets::TYPE_QUICKDIAL,
	'group' => Widgets::GROUP_DASHBOARD,
	'weight' => Widgets::WEIGHT_HIGH
]);

Widgets::register('users', function() use ($t) {
	$total = Users::find('count') + VirtualUsers::find('count');
	$deactivated = Users::find('count', [
		'conditions' => [
			'is_active' => false
		]
	]);

	return [
		'title' => $t('Users'),
		'url' => [
			'controller' => 'Users', 'library' => 'base_core', 'admin' => true, 'action' => 'index'
		],
		'data' => [
			$t('Total') => $total
		]
	];
}, [
	'type' => Widgets::TYPE_COUNTER,
	'group' => Widgets::GROUP_DASHBOARD,
]);


?>