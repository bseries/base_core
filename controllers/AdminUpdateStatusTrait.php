<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;

trait AdminUpdateStatusTrait {

	public function admin_update_status() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$result = $model::first($this->request->id)->save(
			['status' => $this->request->status],
			['whitelist' => ['status'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully updated status.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to update status.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}
}

?>