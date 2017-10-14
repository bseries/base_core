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

	protected function _renderDownload($basename, $stream) {
		rewind($stream);

		$stat = fstat($stream);
		$this->response->headers('Content-Disposition',  'attachment; filename="' . $basename . '";');
		$this->response->headers('Content-Length', $stat['size']);

		$data = stream_get_contents($stream);
		$this->render(['data' => $data, 'type' => 'binary']);
		// $this->_renderChunked($stream);
	}

	protected function _renderChunked($stream, $chunkSize = 8192) {
		rewind($stream);

		while (!feof($stream)) {
			$chunk = fread($stream, $chunkSize);

			if ($chunk === false) {
				throw new Exception("Failed to read chunk from stream.");
			}
			echo $chunk;
		}
	}

	protected function _downloadBasename($userSlug, $context, $path) {
		$name  = '';
		if ($userSlug) {
			$name .= str_replace('-', '_', $userSlug) . '_';
		}
		$name .= $context . '_';

		// May only have basename in path.
		if (dirname($path) != '.') {
			$name .= str_replace('/', '_', dirname($path)) . '_';
		}
		$name .= pathinfo($path, PATHINFO_BASENAME);

		return $name;
	}

}

?>