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
	// data. Intentionally not using the stat() to determine the Content-Length, this
	// seems to be unreliable: tests show that stats reports 54472 but the actual download
	// size 54477 bytes. Content-Length is not required.
	protected function _renderDownload($stream, $mimeType, $encoding = null, $basename = null) {
		$this->_render['auto'] = false;
		$chunkSize = 8192;

		rewind($stream);

		if ($encoding) {
			header("Content-Type: {$mimeType}; charset={$encoding}");
		} else {
			header("Content-Type: {$mimeType}");
		}
		if ($basename) {
			header("Content-Disposition: attachment; filename=\"{$basename}\";");
		}

		ob_end_flush();

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