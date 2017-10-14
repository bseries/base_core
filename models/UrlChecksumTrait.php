<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use Exception;

trait UrlChecksumTrait {

	// Will fail with absolute URLs and non-transitionable ones.
	// @fixme support for http apis that respond with a md5 header field.
	// hash_file dose not work with streams
	public function isConsistent($entity) {
		if (!$entity->checksum) {
			throw new Exception('Entity has no checksum to compare against.');
		}
		$file = parse_url($entity->url('file'), PHP_URL_PATH);
		return hash_file('md5', $file) === $entity->checksum;
	}

	// hash_file dose not work with streams
	// @fixme make static
	public function calculateChecksum($entity) {
		$file = parse_url($entity->url('file'), PHP_URL_PATH);
		return hash_file('md5', $file);
	}
}

?>