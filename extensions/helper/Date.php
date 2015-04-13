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

namespace base_core\extensions\helper;

use lithium\core\Environment;
use IntlDateFormatter;
use DateTime;
use Exception;

class Date extends \lithium\template\Helper {

	public function format($value, $type, array $options = []) {
		if (!$value) {
			return null;
		}
		$options += [
			'locale' => null,
			'timezone' => null
		];
		$locale = $options['locale'] ?: $this->_locale();
		$timezone = $options['timezone'] ?: $this->_timezone();

		if ($value instanceof DateTime) {
			$date = $value;
		} elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]+:[0-9]+:[0-9]+$/', $value)) {
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
		} elseif (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value)) {
			$date = DateTime::createFromFormat('Y-m-d', $value);
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
			return $formatter->format($date);
		} elseif ($type == 'w3c') {
			return $date->format(DateTime::W3C);
		}
		throw new Exception("Invalid date format type `{$type}`.");
	}

	protected function _locale() {
		return Environment::get('locale');
	}

	protected function _timezone() {
		return Environment::get('timezone');
	}
}

?>