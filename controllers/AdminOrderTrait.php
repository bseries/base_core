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

use base_core\extensions\cms\Settings;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminOrderTrait {

	public function admin_order() {
		extract(Message::aliases());

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