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

		switch ($type) {
			case 'full-date':
				$formatter = new IntlDateFormatter(
					$locale,
					IntlDateFormatter::FULL,
					IntlDateFormatter::NONE,
					$timezone
				);
				return $formatter->format($date);
			case 'long-date':
				$formatter = new IntlDateFormatter(
					$locale,
					IntlDateFormatter::LONG,
					IntlDateFormatter::NONE,
					$timezone
				);
				return $formatter->format($date);
			case 'datetime':
				$formatter = new IntlDateFormatter(
					$locale,
					IntlDateFormatter::SHORT,
					IntlDateFormatter::SHORT,
					$timezone
				);
				return $formatter->format($date);
			case 'date':
				$formatter = new IntlDateFormatter(
					$locale,
					IntlDateFormatter::SHORT,
					IntlDateFormatter::NONE,
					$timezone
				);
				return $formatter->format($date);
			case 'time':
				$formatter = new IntlDateFormatter(
					$locale,
					IntlDateFormatter::NONE,
					IntlDateFormatter::SHORT,
					$timezone
				);
				return $formatter->format($date);
			case 'w3c':
				return $date->format(DateTime::W3C);
		}
	}

	protected function _locale() {
		return Environment::get('locale');
	}

	protected function _timezone() {
		return Environment::get('timezone');
	}
}

?>