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

namespace base_core\extensions\helper;

use lithium\core\Environment;
use IntlDateFormatter;
use DateTime;
use Exception;

class Date extends \lithium\template\Helper {

	/**
	 * Formats date strings and objects into loaclized date strings.
	 *
	 * @param string|\DateTime|integer $value Either:
	 *        - a date as a string (in `'Y-m-d'`  or `'Y-m-d H:i:s'` format)
	 *        - an DateTime object with the date to format
	 *        - or an Unix timestamp
	 * @param string $type On of:
	 *        - the string `'w3c'`
	 *        - a string with a valid datetime format syntax pattern:
	 *          http://userguide.icu-project.org/formatparse/datetime
	 *        - one of the strings:
	 *          - `'time'` for short time only
	 *          - `'date'` for short date only
	 *          - `'full-date'` for full date only
	 *          - `'long-date'` for long date only
	 *          - `'datetime'` for short date and time
	 * @param array $options Available options are:
	 *        - `'locale'` the locale to use for formatting into
	 *        - `'timezone'` the target timezone
	 *        - `'wrap'` allows to wrap the date in an HTML date element.
	 * @return string
	 */
	public function format($value, $type, array $options = []) {
		if (!$value) {
			return null;
		}
		$options += [
			'locale' => null,
			'timezone' => null,
			// Wraps in time HTML element when `true` or array with attributes
			// for the element.
			'wrap' => false
		];
		$locale = $options['locale'] ?: $this->_locale();
		$timezone = $options['timezone'] ?: $this->_timezone();

		if ($value instanceof DateTime) {
			$date = $value;
		} elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]+:[0-9]+:[0-9]+$/', $value)) {
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
		} elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
			$date = DateTime::createFromFormat('Y-m-d', $value);
		} elseif (is_integer($value)) {
			$date = new DateTime('@' . $value);
		} else {
			throw new Exception("Cannot parse date value `{$value}`.");
		}

		$types = [
			'time' => [IntlDateFormatter::NONE, IntlDateFormatter::SHORT],
			'date' => [IntlDateFormatter::SHORT, IntlDateFormatter::NONE],
			'full-date' => [IntlDateFormatter::FULL, IntlDateFormatter::NONE],
			'long-date' => [IntlDateFormatter::LONG, IntlDateFormatter::NONE],
			'datetime' => [IntlDateFormatter::SHORT, IntlDateFormatter::SHORT]
		];
		if (isset($types[$type])) {
			$formatter = new IntlDateFormatter(
				$locale,
				$types[$type][0],
				$types[$type][1],
				$timezone
			);
			$result = $formatter->format($date);
		} elseif ($type == 'w3c') {
			$result = $date->format(DateTime::W3C);
		} else {
			$formatter = new IntlDateFormatter(
				$locale,
				IntlDateFormatter::FULL,
				IntlDateFormatter::FULL,
				$timezone
			);
			$formatter->setPattern($type);
			$result = $formatter->format($date);
		}

		if ($options['wrap']) {
			return sprintf(
				'<time datetime="%s"%s>%s</time>',
				$this->format($value, 'w3c'),
				is_array($options['wrap']) ? $this->_attributes($options['wrap']) : '',
				$result
			);
		}
		return $result;
	}

	protected function _locale() {
		return Environment::get('locale');
	}

	protected function _timezone() {
		return Environment::get('timezone');
	}
}

?>