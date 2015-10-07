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

use base_core\models\Assets;
use lithium\net\http\Media as HttpMedia;

Assets::registerScheme('file', [
	'base' => PROJECT_ASSETS_FILE_BASE
]);

if (defined('PROJECT_ASSETS_HTTP_BASE')) {
	Assets::registerScheme('http', [
		'base' => PROJECT_ASSETS_HTTP_BASE
	]);
}
if (defined('PROJECT_ASSETS_HTTPS_BASE')) {
	Assets::registerScheme('https', [
		'base' => PROJECT_ASSETS_HTTPS_BASE
	]);
}

HttpMedia::type('binary', 'application/octet-stream', [
	'cast' => false,
	'encode' => function($data) {
		return $data;
	}
]);

?>