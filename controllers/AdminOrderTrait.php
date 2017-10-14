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