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

	public function fourohthree() {
		if (INSIDE_ADMIN) {
			return $this->redirect('Users::sesion');
		}
		$this->_render['layout'] = 'error';
		$this->_render['template'] = '403';
		$this->response->status(403);
	}

	public function fourohfour() {
		$this->_render['layout'] = INSIDE_ADMIN ? 'admin_error' : 'error';
		$this->_render['template'] = '404';
		$this->response->status(404);
	}

	public function fiveohoh() {
		$this->_render['layout'] = INSIDE_ADMIN ? 'admin_error' : 'error';
		$this->_render['template'] = '500';
		$this->response->status(500);

		if ($this->request->accepts() === 'json') {
			$response = new JSendResponse();
			$response->error('An unkown error occured.');

			$this->render([
				'type' => $this->request->accepts(),
				'data' => $response->to('array')
			]);
		}
	}

	public function fiveohthree() {
		$this->_render['layout'] = INSIDE_ADMIN ? 'admin_error' : 'error';
		$this->_render['template'] = '503';
		$this->response->status(503);
	}

	public function browser() {
		$this->_render['layout'] = INSIDE_ADMIN ? 'admin_error' : 'error';
		$this->_render['template'] = 'browser';
		$this->response->status(400);
	}

	public function maintenance() {
		$this->_render['layout'] = INSIDE_ADMIN ? 'admin_error' : 'error';
		$this->_render['template'] = 'maintenance';
		$this->response->status(503);
		$this->response->headers['Retry-After'] = 3600; // s; 1 hour
	}
}

?>