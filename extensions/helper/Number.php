<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\extensions\helper;

use lithium\core\Environment;
use NumberFormatter;

class Number extends \lithium\template\Helper {

	public function format($value, $type, array $options = []) {
		$options += [
			'locale' => null
		];
		$locale = $options['locale'] ?: $this->_locale();

		switch ($type) {
			case 'decimal':
				$formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
				return $formatter->format($value);
		}
	}

	protected function _locale() {
		return Environment::get('locale');
	}
}

?>