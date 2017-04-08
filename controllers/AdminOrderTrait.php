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
 * License. If not, see https://atelierdisko.de/licenses.
 */

namespace base_core\controllers;

use AD\jsend\Response as JSendResponse;
use base_core\extensions\cms\Settings;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminOrderTrait {

	public function admin_api_order() {
		extract(Message::aliases());

		$response = new JSendResponse();

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$ids = $this->request->data['ids'];

		if ($model::hasBehavior('Ownable') && Settings::read('security.checkOwner')) {
			if (!Gate::checkRight('owner')) {
				foreach ($ids as $id) {
					Gate::owned($model::find($id), ['require' => true]);
				}
			}
		}

		if ($model::weightSequence($ids)) {
			$model::pdo()->commit();
			$response->success();
		} else {
			$model::pdo()->rollback();
			$response->error('Failed to update order.');
		}
		return $this->render([
			'type' => $this->request->accepts(),
			'data' => $response->to('array')
		]);
	}
}

?>