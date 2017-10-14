<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use AD\jsend\Response as JSendResponse;

class ErrorsController extends \base_core\controllers\BaseController {

	protected $_render = [
		'layout' => 'admin_error'
	];

	public function admin_generic() {}

	public function admin_fourohthree() {
		return $this->redirect([
			'controller' => 'Users',
			'action' => 'session',
			'library' => 'base_core',
			'admin' => true
		]);
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