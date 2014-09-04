<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\net\http\Router;
use lithium\core\Libraries;

class AppController extends \base_core\controllers\BaseController {

	public function admin_api_discover() {
		$data = [];

		if (Libraries::get('base_media')) {
			$base = ['controller' => 'media', 'library' => 'base_media', 'admin' => true];
			$data += [
				'media:index' => Router::match($base + ['action' => 'api_index'], $this->request),
				'media:view' => Router::match($base + ['action' => 'api_view', 'id' => '__ID__'], $this->request),
				'media:transfer-preflight' => Router::match($base + ['action' => 'api_transfer_preflight'], $this->request),
				'media:transfer-meta' => Router::match($base + ['action' => 'api_transfer_meta'], $this->request),
				'media:transfer' => Router::match($base + ['action' => 'api_transfer'], $this->request) . '?title=__TITLE__'
			];
		}

		$data += [
			'widgets:view' => Router::match([
				'controller' => 'widgets', 'library' => 'base_core',
				'action' => 'api_view', 'name' => '__NAME__', 'admin' => true
			], $this->request),
		];

		$this->render(array('type' => $this->request->accepts(), 'data' => $data));
	}
}

?>