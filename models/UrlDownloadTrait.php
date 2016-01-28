<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see http://atelierdisko.de/licenses.
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
				$stream = fopen($file, 'w+');
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