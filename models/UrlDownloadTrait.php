<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\models;

use Exception;
use lithium\storage\Cache;
use lithium\analysis\Logger;
use temporary\Manager as Temporary;

trait UrlDownloadTrait {

	public function download($entity) {
		$file = Temporary::file(['context' => 'download']);

		switch (parse_url($entity->url, PHP_URL_SCHEME)) {
			case 'http':
			case 'https':
				if (!$stream = fopen($file, 'w+')) {
					throw new Exception("Failed to open `{$file}` for writing.");
				}
				$cacheKey = 'url_download_' . md5($entity->url);

				if ($cached = Cache::read('blob', $cacheKey)) {
					stream_copy_to_stream($cached, $stream);
					fclose($cached);
				} else {
					Logger::debug("Downloading `{$entity->url}` -> `{$file}`.");

					$curl = curl_init($entity->url);

					curl_setopt($curl, CURLOPT_FILE, $stream);
					curl_setopt($curl, CURLOPT_HEADER, 0);

					if (!curl_exec($curl)) {
						throw new Exception("Failed to download `{$entity->url}` -> `{$file}`.");
					}
					curl_close($curl);

					rewind($stream);
					Cache::write('blob', $cacheKey, $stream);
				}
				fclose($stream);
				break;
			case 'file': // Fake download, download is copy.
				if (!copy($entity->url, $file)) {
					throw new Exception("Failed to copy `{$entity->url}` -> `{$file}`.");
				}
				break;
			default:
				throw new Exception("Cannot download `{$entity->url}`; wrong scheme.");
		}
		return 'file://' . $file;
	}
}

?>