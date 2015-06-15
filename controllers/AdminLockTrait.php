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
use li3_access\security\AccessDeniedException;

trait AdminLockTrait {

	public function admin_lock() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($user['role'] !== 'admin' && !$item->isOwner($user)) {
			throw new AccessDeniedException();
		}

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

			FlashMessage::write($t('Successfully locked.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to lock.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}

	public function admin_unlock() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($user['role'] !== 'admin' && !$item->isOwner($user)) {
			throw new AccessDeniedException();
		}

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

			FlashMessage::write($t('Successfully unlocked.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to unlock.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}
}

?>