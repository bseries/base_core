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

namespace base_core\config\bootstrap;

use li3_mailer\net\mail\Delivery;

$config = [
	'types' => explode(' ', PROJECT_MAIL_TYPES),
	'from' => PROJECT_MAIL_FROM
];

if (PROJECT_DEBUG) {
	$config += [
		'adapter' => 'Debug',
		'log' => PROJECT_PATH . '/log/mail.log'
	];
} else {
	$config += [
		'adapter' => 'Mailgun',
		'key' => PROJECT_MAIL_KEY,
		'domain' => PROJECT_MAIL_DOMAIN,
		'dkim' => 'yes'
	];
}

if (PROJECT_MAIL_TEST) {
	$config += [
		'testmode' => true
	];
}

Delivery::config(['default' => $config]);

?>