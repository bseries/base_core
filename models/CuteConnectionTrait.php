<?php
/**
 * Copyright 2017 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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