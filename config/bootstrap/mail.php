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

namespace base_core\config\bootstrap;

use li3_mailer\net\mail\Delivery;

$config = [
	'types' => explode(' ', PROJECT_MAIL_TYPES),
	// The default sender, can be changed on a
	// per mailing basis inside i.e. Controllers.
	'from' => 'noreply@' . PROJECT_MAIL_DOMAIN
];

// When we are in debug mode, all mail goes to a log file.
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

// Debug mode must be of to make this take effect.
if (PROJECT_MAIL_TEST) {
	$config += [
		'testmode' => true
	];
}

Delivery::config(['default' => $config]);

?>