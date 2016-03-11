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

use AD\jsend\Response as JSendResponse;

class ErrorsController extends \base_core\controllers\BaseController {

	public function admin_generic() {}

	public function admin_fourohthree() {
		return $this->redirect('Users::session');
	}

	// public function admin_fourohfour() {}

	public function admin_fiveohoh() {
		if ($this->request->accepts() === 'json') {
			$response = new JSendResponse();
			$response->error('An unkown error occured.');

			$this->render([
				'type' => $this->request->accepts(),
				'data' => $response->to('array')
			]);
		}
	}
}

?>