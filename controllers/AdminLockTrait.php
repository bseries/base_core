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

trait AdminLockTrait {

	public function admin_lock() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		$result = $item->save(
			['is_locked' => true],
			[
				'whitelist' => ['is_locked'],
				'validate' => false,
				'lockWriteThrough' => true
			]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully locked.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to lock.'), ['level' => 'error']);
			return $this->redirect($this->request->referer());
		}
		$url = ['action' => 'index', 'library' => $this->_library];

		if ($redirectUrl = $this->_redirectUrl($item)) {
			$url = $redirectUrl + $url;
		}
		return $this->redirect($url);
	}

	public function admin_unlock() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		$result = $item->save(
			['is_locked' => false],
			[
				'whitelist' => ['is_locked'],
				'validate' => false,
				'lockWriteThrough' => true
			]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully unlocked.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to unlock.'), ['level' => 'error']);
			return $this->redirect($this->request->referer());
		}
		$url = ['action' => 'index', 'library' => $this->_library];

		if ($redirectUrl = $this->_redirectUrl($item)) {
			$url = $redirectUrl + $url;
		}
		return $this->redirect($url);
	}
}

?>