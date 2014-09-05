<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

use lithium\core\ErrorHandler;
use lithium\core\Libraries;
use lithium\core\Environment;
use lithium\analysis\Logger;
use lithium\util\String;
use lithium\data\Connections;
use lithium\action\Dispatcher;
use lithium\action\Request;
use lithium\action\Response;
use lithium\net\http\Media;

$path = dirname(Libraries::get(true, 'path'));
ini_set('error_reporting', E_ALL);

if (Environment::is('production')) {
	ini_set('display_errors', false);
} else {
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
	$message  = String::insert("Error ({:type})\nMessage : {:message}\nLine    : {:line}\nFile    : {:file}", $info);
	$message .= "\nTrace   :\n" . $formatTrace($info['trace']) . "";

	Logger::error($message);

	return Environment::is('production');
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
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

if (Environment::is('development')) {
	$run = new Run();

	$run->pushHandler(new PrettyPageHandler());

	$handler = new JsonResponseHandler();
	$handler->onlyForAjaxRequests(true);
	$run->pushHandler($handler);

	$run->register();
}

if (USE_LOGGING) {
	Logger::config([
		'default' => [
			'adapter' => 'File',
			'path' => $path . '/log',
			'timestamp' => '[Y-m-d H:i:s]',
			// Log everything into one file.
			'file' => function($data, $config) { return 'app.log'; },
			'priority' => ['debug', 'error', 'notice', 'warning']
		],
	]);
}

// Handle errors rising from exceptions.
$errorResponse = function($request, $code) {
	$request = new Request([
		'url' => '/' . $code
	]);
	return Dispatcher::run($request);
};

if (!Environment::is('development')) {
	Dispatcher::applyFilter('run', function($self, $params, $chain) use ($errorResponse){
		try {
			return $chain->next($self, $params, $chain);
		} catch (\Exception $e) {
			$message  = 'Catching exception and showing error response';
			$message .= ' ;code was `' . $e->getCode() . '`';
			$message .= ' ;message was `' . $e->getMessage() . '`.';
			Logger::debug($message);

			$controller = Libraries::instance('controllers', 'base_core.Errors', ['request' => $params['request']]);

			$map = [
				500 => 'fiveohoh',
				404 => 'fourohfour',
				403 => 'fourohthree'
			];
			return $controller(
				$params['request'],
				['action' => $map[$e->getCode() ?: 500]]
			);
		}
	});
}


?>