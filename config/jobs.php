<?php
/**
 * Base Core
 *
 * Copyright (c) 2016 Atelier Disko - All rights reserved.
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

namespace base_core\config;

use base_core\async\Jobs;
use lithium\storage\Cache;
use lithium\analysis\Logger;

Jobs::recur('base_core:gc_cache', function() {
	$start = microtime(true);

	foreach (['default', 'blob'] as $name) {
		Cache::clean($name);
	}

	$took = round((microtime(true) - $start), 2);
	Logger::debug("GC on caches done, took {$took}s.");
}, [
	'frequency' => Jobs::FREQUENCY_LOW
]);

?>