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

use base_core\extensions\cms\Settings;

Settings::register('site.title');

// FIXME Use a pseudo number generator seeded with project
// name to generate cookie secret. Simple md5'ing wont work as
// there the alphabet would be too limited for a password style string.
Settings::register('security.cookieSecret', 'alsFDDT§$sdfs');

// Enable checking of ownership. When enabled everybody
// else than users with the `'owner'` privilege can only view or
// edit entities owned by them. When disabled ownership is still
// assigned **and kept**, but there is no restriction on who can
// see and edit what also the form elements are never displayed.
Settings::register('security.checkOwner', false);

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
	'organization' => 'Atelier Disko',
	'address_line_1' => 'Budapester Straße 49',
	'locality' => 'Hamburg',
	'postal_code' => '20359',
	'country' => 'DE',
	'dependent_locality' => 'St. Pauli',
	'website' => 'http://atelierdisko.de',
	'email' => 'info@atelierdisko.de',
	'phone' => null,
]);

Settings::register('service.googleAnalytics.default.account');
Settings::register('service.googleAnalytics.default.domain');
Settings::register('service.googleAnalytics.default.propertyId');
Settings::register('service.googleAnalytics.default.useUniversalAnalytics', false);

Settings::register('user.number', [
	'sort' => '/([0-9]{4}-[0-9]{4})/',
	'extract' => '/[0-9]{4}-([0-9]{4})/',
	'generate' => '%Y-%%04.d'
]);

Settings::register('user.sendActivationMail', false);

Settings::register('user.useBecome', false);
Settings::register('user.useVirtualUsers', false);

?>