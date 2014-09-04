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

trait AdminPublishTrait {

	public function admin_publish() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$result = $model::first($this->request->id)->save(
			['is_published' => true],
			['whitelist' => ['is_published'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully published.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to publish.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}

	public function admin_unpublish() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$result = $model::first($this->request->id)->save(
			['is_published' => false],
			['whitelist' => ['is_published'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully unpublished.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to unpublish.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}
}

?>