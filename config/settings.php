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

use base_core\extensions\cms\Settings;

Settings::register('site.title');

// FIXME Use a pseudo number generator seeded with project
// name to generate cookie secret. Simple md5'ing wont work as
// there the alphabet would be too limited for a password style string.
Settings::register('security.cookieSecret', 'alsFDDT§$sdfs');

Settings::register('contact.defalt', [
	'name' => 'Acme Inc.',
	'type' => 'organization',
	'email' => 'mail@example.com',
	'phone' => '+49 (0) 12 345 678',
	'postal_code' => '12345',
	'street_address' => 'Boulevard of Dreams 23',
	'city' => 'Las Vegas',
	'country' => 'USA'
]);

Settings::register('contact.exec', [
	'name' => 'Atelier Disko',
	'type' => 'organization',
	'email' => 'info@atelierdisko.de',
	'phone' => '+49 (0) 40 355 618 96',
	'website' => 'http://atelierdisko.de',
	'postal_code' => 'D-20359',
	'street_address' => 'Budapester Straße 49',
	'city' => 'Hamburg',
	'country' => 'Germany',
	'district' => 'St. Pauli'
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

Settings::register('availableCurrencies', [
	'EUR', 'USD'
]);

?>