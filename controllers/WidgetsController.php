<?php
/**
 * Bureau Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace cms_core\controllers;

use lithium\net\http\Router;
use cms_core\extensions\cms\Widgets;
use jsend\Response as JSendResponse;
use lithium\analysis\Logger;

class WidgetsController extends \cms_core\controllers\BaseController {

	public function admin_api_view() {
		$start = microtime(true);
		$item = Widgets::read($this->request->name);

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

		$this->render(array(
			'type' => $this->request->accepts(),
			'data' => $response->to('array')
		));
	}
}

?>