<?php
/**
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config;

use base_core\async\Jobs;
use lithium\storage\Cache;
use lithium\analysis\Logger;

Jobs::recur('base_core:gc_cache', function() {
	$start = microtime(true);

	foreach (['default', 'blob'] as $name) {
		if (!Cache::clean($name)) {
			return false;
		}
	}

	$took = round((microtime(true) - $start), 2);
	Logger::debug("GC on caches done, took {$took}s.");

	return true;
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

?>