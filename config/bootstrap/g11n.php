<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

/**
 * This bootstrap file contains configurations for all globalizing
 * aspects of your application.
 */
use li3_mailer\net\mail\Media as MailMedia;
use lithium\action\Dispatcher as ActionDispatcher;
use lithium\aop\Filters;
use lithium\console\Dispatcher as ConsoleDispatcher;
use lithium\core\Environment;
use lithium\core\Libraries;
use lithium\g11n\Catalog;
use lithium\g11n\Locale;
use lithium\g11n\Message;
use lithium\g11n\Multibyte;
use lithium\net\http\Media;
use lithium\security\Auth;
use lithium\util\Inflector;
use lithium\util\Validator;

/**
 * Dates
 *
 * Sets the default timezone used by all date/time functions.
 */
date_default_timezone_set('UTC');
Environment::set(true, ['timezone' => PROJECT_TIMEZONE]);

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
Environment::set(true, ['locale' => PROJECT_LOCALE]);

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
$setLocale = function($params, $next) {
	$request =& $params['request'];

	// We cannot yet switch the locale early enough (or load config files late enough), so
	// that we can use the user's locale for auto translated admin.
	if (INSIDE_ADMIN) {
		return $next($params);
	}

	// Timezone
	if (PHP_SAPI !== 'cli' && ($user = Auth::check('default'))) {
		$timezone = $user['timezone'];
	}
	Environment::set(true, compact('timezone'));

	// Locale
	if (!empty($request->locale)) { // Explitic locale overrides anything.
		$locale = $request->locale;
	} elseif (!empty($request->language)) { // Locale may also be composed.
		$locale = $request->language;

		if (!empty($request->country)) {
			$locale .= '_' . $request->country;
		}
	} else { // Autodetect locale.
		try {
			$locale = Locale::preferred($request, explode(' ', PROJECT_LOCALES));
		} catch (\Exception $e) {
			$locale = null;
		}
		if (!$locale) {
			// Locale was in available locales or could not be parsed.
			$locale = PROJECT_LOCALE;
		}
	}

	// For translation, we're working with just the language part.
	$locale = Locale::decompose($locale)['language'];

	Environment::set(true, ['locale' => $locale]);
	$request->locale($locale); // For BC, not used elsewhere.

	return $next($params);
};
Filters::apply(ActionDispatcher::class, '_callable', $setLocale);
Filters::apply(ConsoleDispatcher::class, '_callable', $setLocale);

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
	'lithium' => [
		'adapter' => 'Php',
		'path' => PROJECT_PATH . '/app/libraries/lithium/g11n/resources/php'
	]
] + Catalog::config());
// Catalogs of app and  modules are boostrapped elsewhere.

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
Filters::apply(Media::class, '_handle', function($params, $next) {
	$params['handler'] += ['outputFilters' => []];
	$params['handler']['outputFilters'] += Message::aliases();
	return $next($params);
});
Filters::apply(MailMedia::class, '_handle', function($params, $next) {
	$params['handler'] += ['outputFilters' => []];
	$params['handler']['outputFilters'] += Message::aliases();
	return $next($params);
});

?>