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

use base_core\extensions\cms\Settings;
use base_core\models\Assets;
use lithium\net\http\Media as HttpMedia;

// Enables the Sites framework feature for multi site support. Allows to place content
// in different sites hosted by the same app. Each site must be registered via
// base_core\base\Sites.
Settings::register('useSites', false);

//
// General Settings
//

// Enables in-admin support button.
Settings::register('contactSupport', [
	'enabled' => true,
	'url' => 'http://atelierdisko.de/clients/tickets/add'
]);

// Enable checking of ownership module wide. When enabled everybody
// else than users with the `'owner'` privilege can only view or
// edit entities owned by them. When disabled ownership is still
// assigned **and kept**, but there is no restriction on who can
// see and edit what also the form elements are never displayed.
Settings::register('security.checkOwner', false);

// When enabled the user will be notified when her account
// is activated.
Settings::register('user.sendActivationMail', false);

// Enables the `become` feature. Allows to become another user
// i.e. to create an order in the name of somebody else.
Settings::register('user.useBecome', false);

// How to generate user reference numbers.
Settings::register('user.number', [
	'sort' => '/([0-9]{4}-[0-9]{4})/',
	'extract' => '/[0-9]{4}-([0-9]{4})/',
	'generate' => '%Y-%%04.d'
]);

//
// Contacts
//
Settings::register('contact.default', [
	// 'organization' => 'Acme Inc.',
	// 'postal_code' => '12345',
	// 'address_line_1' => 'Boulevard of Dreams 23',
	// 'locality' => 'Las Vegas',
	// 'country' => 'USA',
	// 'email' => 'mail@example.com',
	// 'phone' => '+49 (0) 12 345 678'
]);

Settings::register('contact.exec', [
	'organization' => 'Atelier Disko UG (haftungsbeschränkt) & Co. KG',
	'address_line_1' => 'Weidenallee 10b',
	'locality' => 'Hamburg',
	'postal_code' => '20357',
	'country' => 'DE',
	'website' => 'https://atelierdisko.de',
	'email' => 'info@atelierdisko.de',
	'phone' => null
]);

//
// Services
//
Settings::register('service.googleAnalytics.default', [
	'account' => null,
	'domain' => null,
	'useUniversalAnalytics' => false
]);

//
// Assets/Media
//
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

// Do not touch binary media.
HttpMedia::type('binary', 'application/octet-stream', [
	'cast' => false,
	'encode' => function($data) {
		return $data;
	}
]);

?>