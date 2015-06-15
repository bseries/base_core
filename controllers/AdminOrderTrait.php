<?php
/**
 * Base
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\security\Auth;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

trait AdminOrderTrait {

	public function admin_order() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$ids = $this->request->data['ids'];

		if ($user['role'] !== 'admin') {
			foreach ($ids as $id) {
				if (!$model::find($id)->isOwner($user)) {
					throw new AccessDeniedException();
				}
			}
		}

		if ($model::weightSequence($ids)) {
			$model::pdo()->commit();

			FlashMessage::write($t('Successfully updated order.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to update order.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		}
		return $this->render(['head' => true]);
	}
}

?>