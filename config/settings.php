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

Settings::register('contact.default.name', 'Acme Inc.');
Settings::register('contact.default.type', 'organization'); // organization or person
Settings::register('contact.default.email', 'mail@example.com');
Settings::register('contact.default.phone', '+49 (0) 12 345 678');
Settings::register('contact.default.city', 'Las Vegas');

Settings::register('contact.exec.name', 'Atelier Disko');
Settings::register('contact.exec.type', 'organization');
Settings::register('contact.exec.email', 'info@atelierdisko.de');
Settings::register('contact.exec.phone', '+49 (0) 40 355 618 96');
Settings::register('contact.exec.website', 'http://atelierdisko.de');
Settings::register('contact.exec.postal_code', 'D-20359');
Settings::register('contact.exec.street_address', 'Budapester Straße 49');
Settings::register('contact.exec.city', 'Hamburg');
Settings::register('contact.exec.country', 'Germany');
Settings::register('contact.exec.district', 'St. Pauli');

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

Settings::register('availableCountries', [
	'DE', 'US', 'CA'
]);

Settings::register('availableCurrencies', [
	'EUR', 'USD'
]);


?>