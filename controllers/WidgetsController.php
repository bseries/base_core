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

namespace base_core\controllers;

use lithium\net\http\Router;
use base_core\extensions\cms\Widgets;
use AD\jsend\Response as JSendResponse;
use lithium\analysis\Logger;

class WidgetsController extends \base_core\controllers\BaseController {

	public function admin_api_view() {
		$start = microtime(true);
		$item = Widgets::read($this->request->id);

		$response = new JSendResponse();

		$data = $item['inner']();
		if (!empty($data['url'])) {
			$data['url'] = Router::match($data['url'], $this->request);
		}
		$response->success($data);

		if (($took = microtime(true) - $start) > 1) {
			$message = sprintf(
				"Widget`{$item['name']}` took very long (%4.fs) to render",
				$took
			);
			Logger::write('notice', $message);
		}

		$this->render([
			'type' => $this->request->accepts(),
			'data' => $response->to('array')
		]);
	}
}

?>