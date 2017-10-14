<?php
/**
 * Copyright 2015 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
 */

namespace base_core\controllers;

use base_core\extensions\cms\Jobs;
use AD\jsend\Response as JSendResponse;
use base_core\extensions\net\http\InternalServerErrorException;

class JobsController extends \base_core\controllers\BaseController {

	// This is the HTTP interface for executing jobs, it is an alternative
	// to cronjobs based invocation of the jobs command. It's protected
	// by token based auth to prevent easy-dos'ing through expensive job
	// invocation.
	public function admin_api_run() {
		if (PROJECT_SCHEDULED_JOBS !== 'http') {
			throw new InternalServerErrorException('HTTP API for scheduled jobs is disabled.');
		}
		$response = new JSendResponse();

		$result = Jobs::runFrequency($this->request->frequency);

		if ($result) {
			$response->success();
		} else {
			$response->fail();
		}
		$this->render([
			'type' => $this->request->accepts(),
			'data' => $response->to('array')
		]);
	}
}

?>