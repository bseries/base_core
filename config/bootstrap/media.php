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
use lithium\net\http\Media;
use lithium\core\Libraries;

Collection::formats('lithium\net\http\Media');

Media::type('html', 'text/html', [
	'view' => 'lithium\template\View',
	'paths' => [
		'template' => [
			// Replace base_core views with app ones. This is needed for at least the error
			// templates and might be restricted to just those later.
			Libraries::get('app', 'path') . '/views/{:controller}/{:template}.{:type}.php',
			Libraries::get('base_core', 'path') . '/views/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
		],
		'layout'   => [
			// Force base_core layouts.
			Libraries::get('base_core', 'path') . '/views/layouts/{:layout}.{:type}.php',
			Libraries::get('app', 'path') . '/views/layouts/{:layout}.{:type}.php',
			'{:library}/views/layouts/{:layout}.{:type}.php'
		],
		// 'element'  => '{:library}/views/elements/{:template}.{:type}.php'
	]
]);

?>