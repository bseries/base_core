<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\config\bootstrap;

use lithium\console\Dispatcher;

/**
 * This filter will convert {:heading} to the specified color codes. This is useful for colorizing
 * output and creating different sections.
 *
 */
Dispatcher::applyFilter('_call', function($self, $params, $chain) {
	$params['callable']->response->styles(array(
		'heading' => '\033[1;30;46m'
	));
	return $chain->next($self, $params, $chain);
});

?>