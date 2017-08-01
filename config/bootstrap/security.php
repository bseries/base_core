<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\config\bootstrap;

use Exception;
use lithium\action\Dispatcher;
use lithium\aop\Filters;
use lithium\security\validation\FormSignature;
use base_core\base\Sites;

if (strlen(PROJECT_SECRET_BASE) < 20) {
	throw new Exception('PROJECT_SECRET_BASE is less than 20 chars.');
}

FormSignature::config([
	'secret' => hash('sha512', PROJECT_SECRET_BASE)
]);

/**
 * This filter protects against HTTP host header attacks, by matching the `Host` header
 * sent by the client against a known list of good hostnames. You'll need to modify
 * the list of hostnames inside the filter before using it.
 *
 * @link http://li3.me/docs/book/manual/1.x/quality-code/security
 * @link http://www.skeletonscribe.net/2013/05/practical-http-host-header-attacks.html
 */
Filters::apply(Dispatcher::class, 'run', function($params, $next) {
	foreach (Sites::registry(true) as $site) {
		$fqdn = $site->fqdn('drop');
		$host = $params['request']->host;

		if ($host === $fqdn || $host === "www.{$fqdn}") {
			return $next($params);
		}
	}
	throw new Exception('Suspicious operation detected.');
});

?>