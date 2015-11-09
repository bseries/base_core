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
use lithium\analysis\Logger;
use temporary\Manager as Temporary;

trait UrlDownloadTrait {

	// FIXME Change Temporary so that it accepts kind of a cache key,
	// and we can prevent redownloading the same file.
	public function download($entity) {
		$temporary = Temporary::file(['context' => 'download']);

		Logger::debug("Downloading into temporary `{$temporary}`.");

		if (strpos($entity->url, 'http') === 0) {
			$curl = curl_init($entity->url);
			$file = fopen($temporary, 'w');

			curl_setopt($curl, CURLOPT_FILE, $file);
			curl_setopt($curl, CURLOPT_HEADER, 0);

			$result = curl_exec($curl);
			curl_close($curl);
			fclose($file);
		} else {
			$result = copy($entity->url, $temporary);
		}
		if (!$result) {
			$message = "Could not copy from source {$entity->url} to temporary {$temporary}.";
			throw new Exception($message);
		}
		return 'file://' . $temporary;
	}
}

?>