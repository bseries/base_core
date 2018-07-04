<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\config\bootstrap;

use lithium\action\Dispatcher;
use lithium\action\Request;
use lithium\action\Response;
use lithium\analysis\Logger;
use lithium\aop\Filters;
use lithium\core\ErrorHandler;
use lithium\core\Libraries;
use lithium\data\Connections;
use lithium\net\http\Media;
use lithium\util\Text;

$path = dirname(Libraries::get(true, 'path'));
ini_set('error_reporting', E_ALL);

if (PROJECT_DEBUG) {
	ini_set('display_startup_errors', true);
	ini_set('display_errors', true);
}

$mapErrorType = function($type) {
	switch($type) {
		case E_ERROR: // 1 //
			return 'E_ERROR';
		case E_WARNING: // 2 //
			return 'E_WARNING';
		case E_PARSE: // 4 //
			return 'E_PARSE';
		case E_NOTICE: // 8 //
			return 'E_NOTICE';
		case E_CORE_ERROR: // 16 //
			return 'E_CORE_ERROR';
		case E_CORE_WARNING: // 32 //
			return 'E_CORE_WARNING';
		case E_CORE_ERROR: // 64 //
			return 'E_COMPILE_ERROR';
		case E_CORE_WARNING: // 128 //
			return 'E_COMPILE_WARNING';
		case E_USER_ERROR: // 256 //
			return 'E_USER_ERROR';
		case E_USER_WARNING: // 512 //
			return 'E_USER_WARNING';
		case E_USER_NOTICE: // 1024 //
			return 'E_USER_NOTICE';
		case E_STRICT: // 2048 //
			return 'E_STRICT';
		case E_RECOVERABLE_ERROR: // 4096 //
			return 'E_RECOVERABLE_ERROR';
		case E_DEPRECATED: // 8192 //
			return 'E_DEPRECATED';
		case E_USER_DEPRECATED: // 16384 //
			return 'E_USER_DEPRECATED';
		default:
			return 'UNKNOWN';
	}
};

$handler = function($info) use ($mapErrorType, $path) {
	$formatTrace = function($data) use ($path) {
		$result = '';
		foreach ($data as $line) {
			$line += ['class' => '-', 'type' => '?', 'line' => '?', 'file' => '?'];
			$result .= sprintf("%-40s%-30s on %4d in %s\n",
				$line['class'],
				$line['function'] . '()',
				$line['line'],
				str_replace($path . '/', '', $line['file'])
			);
		}
		return $result;
	};
	if (is_numeric(($info['type']))) {
		$info['type'] = $mapErrorType($info['type']);
	}
	$message  = Text::insert("Error ({:type})\nMessage : {:message}\nLine    : {:line}\nFile    : {:file}", $info);
	$message .= "\nTrace   :\n" . $formatTrace($info['trace']) . "";

	Logger::error($message);

	return !PROJECT_DEBUG;
};
$errorHandler = function($code, $message, $file, $line = 0, $context = null) use ($handler) {
	$trace = debug_backtrace();
	$trace = array_slice($trace, 1, count($trace));
	$type = $code;
	return $handler(compact('type', 'code', 'message', 'file', 'line', 'trace', 'context'));
};

$exceptionHandler = function($exception, $return = false) use ($handler) {
	if (ob_get_length()) {
		ob_end_clean();
	}
	$info = compact('exception') + [
		'type' => get_class($exception),
		'stack' => ErrorHandler::trace($exception->getTrace())
	];
	foreach (['message', 'file', 'line', 'trace'] as $key) {
		$method = 'get' . ucfirst($key);
		$info[$key] = $exception->{$method}();
	}
	if (!$handler($info)) {
		throw $exception;
	}
	return true;
};

// set_error_handler($errorHandler);
// set_exception_handler($exceptionHandler);

if (PROJECT_LOGGING) {
	$priority = ['error', 'notice', 'warning'];

	if (PROJECT_DEBUG_LOGGING || PROJECT_DEBUG) {
		$priority[] = 'debug';
	}
	if (PROJECT_HAS_SYSLOG) {
		Logger::config([
			'default' => [
				'adapter' => 'Syslog',
				'identity' => PROJECT_NAME . '@' . PROJECT_CONTEXT,
				'priority' => $priority
			],
		]);
	} else {
		Logger::config([
			'default' => [
				'adapter' => 'File',
				'path' => $path . '/log',
				'timestamp' => 'Y-m-d H:i:s',
				'format' => "[{:timestamp}] [{:priority}] {:message}\n",
				// Log everything into one file.
				'file' => function($data, $config) { return 'app.log'; },
				'priority' => $priority
			],
		]);
	}
}

if (!PROJECT_DEBUG) {
	// Handle errors rising from exceptions.
	$errorResponse = function($request, $code) {
		$request = new Request([
			'url' => '/' . $code
		]);
		return Dispatcher::run($request);
	};

	// Generally two error controllers to render error pages exist.
	//
	// For the admin scope there is `\base_core\controller\ErrorsController` and
	// for the app, the distro MUST provide `\app\controller\ErrorsController`.
	// Depend on the INSIDE_ADMIN constant requests are dispatched to these
	// controllers.
	Filters::apply(Dispatcher::class, 'run', function($params, $next) use ($errorResponse){
		try {
			return $next($params);
		} catch (\Exception $e) {
			$errorId = Text::uuid();
			$code = $e->getCode();

			$message  = "Caught an exception :)\n";
			$message .= 'type     : ' . get_class($e) . "\n";
			$message .= 'code     : ' . $code . "\n";
			$message .= 'message  : ' . $e->getMessage() . "\n";
			$message .= 'error-id : ' . $errorId . "\n";
			$message .= '-- will invoke error action --';
			Logger::notice($message);

			// Maps status codes to action/template names. PHP methods
			// cannot begin with a number.
			$map = [
				403 => 'fourohthree',
				404 => 'fourohfour',
				500 => 'fiveohoh',
				503 => 'fiveohthree'
			];

			// Searches for an ErrorsController in app and registered libraries. This
			// allows apps to provide their own (subclass) of the errors controller,
			// when modification or redirection of existing error cases is wanted.
			$controller = Libraries::instance('controllers', INSIDE_ADMIN ? 'base_core.Errors' : 'app.Errors', [
				'request' => $params['request'],
				'response' => [
					'status' => isset($map[$code]) ? $code : 500
				],
				'render' => [
					// layout is set in the controllers
					'data' => [
						'code' => $code,
						'errorId' => $errorId
					]
				]
			]);

			// Some error actions may not be activated. Depends on application usage. At a minimum
			// the controller must have a `generic()` action.
			if (isset($map[$code]) && method_exists($controller, (INSIDE_ADMIN ? 'admin_' : '') . $map[$code])) {
				$action = (INSIDE_ADMIN ? 'admin_' : '') . $map[$code];
			} else {
				$action = (INSIDE_ADMIN ? 'admin_' : '') . 'generic';
			}
			return $controller($params['request'], [
				'action' => $action
			]);
		}
	});
}

?>