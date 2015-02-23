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

/**
 * This bootstrap file contains configurations for all globalizing
 * aspects of your application.
 */
use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\g11n\Locale;
use lithium\g11n\Catalog;
use lithium\g11n\Message;
use lithium\g11n\Multibyte;
use lithium\util\Inflector;
use lithium\util\Validator;
use lithium\net\http\Media;
use lithium\action\Dispatcher as ActionDispatcher;
use lithium\console\Dispatcher as ConsoleDispatcher;
use lithium\security\Auth;
use li3_mailer\net\mail\Media as MailMedia;

/**
 * Dates
 *
 * Sets the default timezone used by all date/time functions.
 */
date_default_timezone_set('UTC');

/**
 * Locales
 *
 * Adds globalization specific settings to the environment. The settings for
 * the current locale, time zone and currency are kept as environment settings.
 * This allows for _centrally_ switching, _transparently_ setting and
 * retrieving globalization related settings.
 *
 * The environment settings are:
 *
 *  - `'locale'` The default effective locale.
 *  - `'locales'` Application locales available mapped to names. The available locales are used
 *               to negotiate he effective locale, the names can be used i.e. when displaying
 *               a menu for choosing the locale to users.
 *
 * @see lithiumm\g11n\Message
 * @see lithiumm\core\Environment
 */

Environment::set('test', ['locale' => 'en']);
Environment::set('development', ['locale' => PROJECT_LOCALE]);
Environment::set('staging', ['locale' => PROJECT_LOCALE]);
Environment::set('production', ['locale' => PROJECT_LOCALE]);

/**
 * Effective/Request Locale
 *
 * Intercepts dispatching processes in order to set the effective locale by using
 * the locale of the request or if that is not available retrieving a locale preferred
 * by the client.
 *
 * @see lithiumm\g11n\Message
 * @see lithiumm\core\Environment
 */
$setLocale = function($self, $params, $chain) {
	if (PHP_SAPI !== 'cli' && ($user = Auth::check('default'))) {
		$timezone = $user['timezone'];
	} else {
		$timezone = Environment::get('timezone') ?: PROJECT_TIMEZONE;
	}
	Environment::set(true, compact('timezone'));

	try {
		if (!$params['request']->locale()) {
			$params['request']->locale(Locale::preferred(
				$params['request'], explode(' ', PROJECT_LOCALES)
			));
		}
		if ($locale = $params['request']->locale()) {
			// Locale was in available locales.
			Environment::set(true, ['locale' => $locale]);
		}
	} catch (\Exception $e) {
		// Cannot parse locale. Will continue to use default effective locale.
	}
	return $chain->next($self, $params, $chain);
};
ActionDispatcher::applyFilter('_callable', $setLocale);
ConsoleDispatcher::applyFilter('_callable', $setLocale);

/**
 * Resources
 *
 * Globalization (g11n) catalog configuration.  The catalog allows for obtaining and
 * writing globalized data. Each configuration can be adjusted through the following settings:
 *
 *   - `'adapter'` _string_: The name of a supported adapter. The builtin adapters are `Memory` (a
 *     simple adapter good for runtime data and testing), `Php`, `Gettext`, `Cldr` (for
 *     interfacing with Unicode's common locale data repository) and `Code` (used mainly for
 *     extracting message templates from source code).
 *
 *   - `'path'` All adapters with the exception of the `Memory` adapter require a directory
 *     which holds the data.
 *
 *   - `'scope'` If you plan on using scoping i.e. for accessing plugin data separately you
 *     need to specify a scope for each configuration, except for those using the `Memory`,
 *     `Php` or `Gettext` adapter which handle this internally.
 *
 * @see lithiumm\g11n\Catalog
 * @link https://github.com/UnionOfRAD/li3_lldr
 * @link https://github.com/UnionOfRAD/li3_cldr
 */
Catalog::config([
	'runtime' => [
		'adapter' => 'Memory'
	],
	'app' => [
	 	'adapter' => 'Gettext',
	 	'path' => PROJECT_PATH . '/app/resources/g11n/po'
	 ],
	'lithium' => [
		'adapter' => 'Php',
		'path' => PROJECT_PATH . '/app/libraries/unionofrad/lithium/lithium/g11n/resources/php'
	]
] + Catalog::config());

/**
 * Multibyte Strings
 *
 * Configuration for the `Multibyte` class which allows to work with UTF-8
 * encoded strings. At least one configuration named `'default'` must be
 * present. Available adapters are `Intl`, `Mbstring` and `Iconv`. Please keep
 * in mind that each adapter may act differently upon input containing bad
 * UTF-8 sequences. These differences aren't currently equalized or abstracted
 * away.
 *
 * @see lithiumm\g11n\Multibyte
 */
Multibyte::config([
//	'default' => array('adapter' => 'Intl'),
	'default' => ['adapter' => 'Mbstring'],
//	'default' => array('adapter' => 'Iconv')
]);

/**
 * Transliteration
 *
 * Load locale specific transliteration rules through the `Catalog` class or
 * specify them manually to make `Inflector::slug()` work better with
 * characters specific to a locale.
 *
 * @see lithiumm\g11n\Catalog
 * @see lithium\util\Inflector::slug()
 */
Inflector::rules('transliteration', Catalog::read(true, 'inflection.transliteration', 'en'));
Inflector::rules('transliteration', [
	'/–|—/' => '-',
	'/…/' => '...'
]);

/**
 * Grammar
 *
 * If your application has custom singular or plural rules you can configure
 * that by uncommenting the lines below.
 *
 * @see lithiumm\g11n\Catalog
 * @see lithium\util\Inflector
 */
// Inflector::rules('singular', array('rules' => array('/rata/' => '\1ratus')));
// Inflector::rules('singular', array('irregular' => array('foo' => 'bar')));
// Inflector::rules('plural', array('rules' => array('/rata/' => '\1ratum')));
// Inflector::rules('plural', array('irregular' => array('bar' => 'foo')));
// Inflector::rules('uninflected', 'bord');
// Inflector::rules('uninflected', array('bord', 'baird'));

/**
 * Validation
 *
 * Overwrites certain validation rules in order to make them locale aware. Locale
 * specific versions are added as formats to those rules. In order to validate a
 * german postal code you may use the following configuration in a model.
 *
 * {{{
 * // ...
 *	public $validates = array(
 *		'zip' => array(
 *			array('postalCode', 'format' => 'de_DE')
 *		)
 *		// ...
 * }}}
 *
 * When no format or the special `any` format is provided the rule will use the
 * built-in regular expression. This ensures that default behavior isn't affected.
 *
 * The regular expression for a locale aware rule is retrieved using the `Catalog`
 * class. To add support for more locales and rules have a look at the `li3_lldr`
 * and `li3_cldr` projects.
 *
 *
 * Enables support for multibyte strings through the `Multibyte` class by
 * overwriting rules (currently just `lengthBetween`).
 *
 * @link https://github.com/UnionOfRAD/li3_lldr
 * @link https://github.com/UnionOfRAD/li3_cldr
 * @see lithiumm\g11n\Catalog
 * @see lithiumm\g11n\Multibyte
 * @see lithium\util\Validator
 */
foreach (['phone', 'postalCode', 'ssn'] as $name) {
	$regex = Validator::rules($name);

	Validator::add($name, function($value, $format, $options) use ($name, $regex) {
		if ($format !== 'any') {
			$regex = Catalog::read(true, "validation.{$name}", $format);
		}
		if (!$regex) {
			$message  = "Cannot find regular expression for validation rule `{$name}` ";
			$message .= "using format/locale `{$format}`.";
			throw new RuntimeException($message);
		}
		return preg_match($regex, $value);
	});
}
Validator::add('lengthBetween', function($value, $format, $options) {
	$length = Multibyte::strlen($value);
	$options += ['min' => 1, 'max' => 255];
	return ($length >= $options['min'] && $length <= $options['max']);
});

/**
 * In-View Translation
 *
 * Integration with `View`. Embeds message translation aliases into the `View`
 * class (or other content handler, if specified) when content is rendered. This
 * enables translation functions, i.e. `<?=$t("Translated content"); ?>`.
 *
 * @see lithiumm\g11n\Message::aliases()
 * @see lithiumm\net\http\Media
 */
Media::applyFilter('_handle', function($self, $params, $chain) {
	$params['handler'] += ['outputFilters' => []];
	$params['handler']['outputFilters'] += Message::aliases();
	return $chain->next($self, $params, $chain);
});
MailMedia::applyFilter('_handle', function($self, $params, $chain) {
	$params['handler'] += ['outputFilters' => []];
	$params['handler']['outputFilters'] += Message::aliases();
	return $chain->next($self, $params, $chain);
});

?>