<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

class Assets extends \base_core\models\Base {

	use \base_core\models\SchemeTrait;

	protected $_meta = [
		'connection' => false
	];

	protected static $_defaultScheme = [
		'base' => false
	];
}

?>