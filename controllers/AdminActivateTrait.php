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

trait AdminActivateTrait {

	public function admin_activate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$result = $model::first($this->request->id)->save(
			['is_active' => true],
			['whitelist' => ['is_active'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Activated.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to activate.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}

	public function admin_deactivate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$result = $model::first($this->request->id)->save(
			['is_active' => false],
			['whitelist' => ['is_active'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Deactivated.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to deactivate.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}
}

?>