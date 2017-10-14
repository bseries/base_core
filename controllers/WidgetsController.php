<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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
				"Widget `{$item['name']}` took very long (%4.fs) to render",
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