<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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