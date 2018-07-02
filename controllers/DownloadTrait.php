<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use Exception;

trait DownloadTrait {

	protected function _renderSendfile($file) {
		$this->_render['head'] = true;

		$url = '/protected/' . str_replace(ROOT . '/', '', $file);

		$message = "Delegating download (XSendfile) of file `{$file}` using URL `{$url}`.";
		Logger::write('debug', $message);

		$this->response->headers('X-Accel-Redirect', $url);
	}

	// Opts out of the framework to get full ("low"-level) control over how we send the
	// data.
	protected function _renderDownload($stream, $mimeType, $encoding = null) {
		$this->_render['auto'] = false;
		$chunkSize = 8192;

		rewind($stream);
		$stat = fstat($stream);

		header("Content-Length: {$stat['size']}");
		if ($encoding) {
			header("Content-Type: {$mimeType}; charset={$encoding}");
		} else {
			header("Content-Type: {$mimeType}");
		}

		while (!feof($stream)) {
			$chunk = fread($stream, $chunkSize);

			if ($chunk === false) {
				throw new Exception('Failed to read chunk from stream.');
			}
			echo $chunk;
		}
		$this->_stop();
	}
}

?>