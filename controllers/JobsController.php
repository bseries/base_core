<?php
/**
 * Base Core
 *
 * Copyright (c) 2015 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
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
		if (PROJECT_FEATURE_SCHEDULED_JOBS !== 'http') {
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