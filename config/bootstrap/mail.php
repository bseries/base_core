<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use li3_mailer\action\Mailer;
use li3_mailer\net\mail\Delivery;
use lithium\analysis\Logger;
use lithium\aop\Filters;

$config = [
	'types' => explode(' ', PROJECT_MAIL_TYPES),
	// The default sender, can be changed on a
	// per mailing basis inside i.e. Controllers.
	'from' => PROJECT_MAIL_FROM
];

// When we are in debug mode, all mail goes to a log file.
if (PROJECT_DEBUG) {
	$config += [
		'adapter' => 'Debug',
		'log' => PROJECT_PATH . '/log/mail.log',
		// Only the full format contains both text and html body text.
		'format' => strpos(PROJECT_MAIL_TYPES, 'html') !== false ? 'full' : 'normal'
	];
} else {
	$config += [
		'adapter' => 'Mailgun',
		'key' => PROJECT_MAIL_KEY,
		'domain' => PROJECT_MAIL_DOMAIN,
		'dkim' => 'yes'
	];
}

// Debug mode must be of to make this take effect.
if (PROJECT_MAIL_TEST) {
	$config += [
		'testmode' => true
	];
}

Delivery::config(['default' => $config]);

if (PROJECT_DEBUG_LOGGING || PROJECT_DEBUG) {
	Filters::apply(Mailer::class, 'deliver', function($params, $next) {
		$message  = "About to send mail to `{$params['message']->to}` ";
		$message .= "with subject `{$params['message']->subject}`...";
		Logger::write('debug', $message);

		$result = $next($params);
		if ($result) {
			$message  = "Successfully sent mail to `{$params['message']->to}` ";
			$message .= "with subject `{$params['message']->subject}`.";
			Logger::write('debug', $message);
		} else {
			$message  = "Failed to send mail to `{$params['message']->to}` ";
			$message .= "with subject `{$params['message']->subject}`.";
			Logger::write('notice', $message);
		}
		return $result;
	});
}

?>