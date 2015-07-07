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

use lithium\util\Collection;
use lithium\net\http\Media as HttpMedia;
use li3_mailer\net\mail\Media as MailerMedia;

Collection::formats('lithium\net\http\Media');

$app = PROJECT_PATH . '/app';
$baseCore = PROJECT_PATH . '/app/libraries/base_core';

//
// Override media type definitions to set path search order.
//
// 1. Always search app first, this allows the app to override everything
//    independent if a library was given explicitly in the render call or not.
//
// 2. Let base_core act as fallback for app searching, same rules apply.
//
// 3. Catch cases where library was given.
//
HttpMedia::type('html', 'text/html', [
	'view' => 'lithium\template\View',
	'paths' => [
		'template' => [
			$app . '/views/{:controller}/{:template}.{:type}.php',
			$baseCore . '/views/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
		],
		'layout' => [
			$app . '/views/layouts/{:layout}.{:type}.php',
			$baseCore . '/views/layouts/{:layout}.{:type}.php',
			'{:library}/views/layouts/{:layout}.{:type}.php',
		],
		// 'element'  => '{:library}/views/elements/{:template}.{:type}.php'
	]
]);
MailerMedia::type('text', 'text/plain', [
	'view' => 'li3_mailer\template\Mail',
	'paths' => [
		'template' => [
			$app . '/mails/{:template}.{:type}.php',
			$baseCore . '/mails/{:template}.{:type}.php',
			'{:library}/mails/{:template}.{:type}.php'
		],
		'layout' => [
			$app . '/mails/layouts/{:layout}.{:type}.php',
			$baseCore . '/mails/layouts/{:layout}.{:type}.php',
			'{:library}/mails/layouts/{:layout}.{:type}.php'
		],
		// 'element'  => '{:library}/mails/elements/{:template}.{:type}.php'
	]
]);

?>