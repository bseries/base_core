<?php
/**
 * Base Core
 *
 * Copyright (c) 2017 Atelier Disko - All rights reserved.
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

namespace base_core\models;

use Exception;
use Cute\Connection;
use Monolog\Handler\NullHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use lithium\analysis\Logger;

trait CuteConnectionTrait {

	protected static $_cuteConnection;

	protected static function _cuteConnection() {
		if (static::$_cuteConnection) {
			return static::$_cuteConnection;
		}
		$log = new MonologLogger(PROJECT_NAME);
		$config = Logger::config('default');

		if ($config['adapter'] === 'Syslog') {
			$handler = new SyslogHandler($config['identity']);
		} elseif ($config['adapter'] ==='File') {
			$handler = new StreamHandler(dirname($config['path']) . '/cute.log');
		} else {
			$handler = new NullHandler();
		}
		$log->pushHandler($handler);

		return static::$_cuteConnection = new Connection(
			$log, PROJECT_NAME . '_' . PROJECT_CONTEXT
		);
	}
}

?>