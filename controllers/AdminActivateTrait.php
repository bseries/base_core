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

use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;
use li3_access\security\AccessDeniedException;
use base_core\security\Gate;

trait AdminActivateTrait {

	public function admin_activate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);

		if ($model::hasBehavior('Ownable') && !Gate::check('users') && !Gate::owned($item)) {
			throw new AccessDeniedException();
		}

		$result = $item->save(
			['is_active' => true],
			['whitelist' => ['is_active'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();

			FlashMessage::write($t('Activated.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to activate.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}

	public function admin_deactivate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);

		if ($model::hasBehavior('Ownable') && !Gate::check('users') && !Gate::owned($item)) {
			throw new AccessDeniedException();
		}

		$result = $item->save(
			['is_active' => false],
			['whitelist' => ['is_active'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();

			FlashMessage::write($t('Deactivated.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to deactivate.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}
}

?>