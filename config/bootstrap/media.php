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

use lithium\util\Collection;
use lithium\core\Libraries;
use lithium\net\http\Media as HttpMedia;
use li3_mailer\net\mail\Media as MailerMedia;

Collection::formats('lithium\net\http\Media');

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
			Libraries::get('app', 'path') . '/views/{:controller}/{:template}.{:type}.php',
			Libraries::get('base_core', 'path') . '/views/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
		],
		'layout'   => [
			Libraries::get('app', 'path') . '/views/layouts/{:layout}.{:type}.php',
			Libraries::get('base_core', 'path') . '/views/layouts/{:layout}.{:type}.php',
			'{:library}/views/layouts/{:layout}.{:type}.php',
		],
		// 'element'  => '{:library}/views/elements/{:template}.{:type}.php'
	]
]);
MailerMedia::type('text', 'text/plain', [
	'view' => 'li3_mailer\template\Mail',
	'paths' => [
		'template' => [
			Libraries::get('app', 'path') . '/mails/:template}.{:type}.php',
			Libraries::get('base_core', 'path') . '/mails/{:template}.{:type}.php',
			'{:library}/mails/{:template}.{:type}.php'
		],
		'layout'   => [
			Libraries::get('app', 'path') . '/mails/layouts/{:layout}.{:type}.php',
			Libraries::get('base_core', 'path') . '/mails/layouts/{:layout}.{:type}.php',
			'{:library}/mails/layouts/{:layout}.{:type}.php'
		],
		// 'element'  => '{:library}/mails/elements/{:template}.{:type}.php'
	]
]);

?>