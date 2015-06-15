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

use base_core\security\Gate;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;
use li3_access\security\AccessDeniedException;

trait AdminPromoteTrait {

	public function admin_promote() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($model::hasBehavior('Ownable') && !Gate::check('users') && !Gate::owned($item)) {
			throw new AccessDeniedException();
		}

		$result = $item->save(
			['is_promoted' => true],
			['whitelist' => ['is_promoted'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully promoted.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to promote.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}

	public function admin_unpromote() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($model::hasBehavior('Ownable') && !Gate::check('users') && !Gate::owned($item)) {
			throw new AccessDeniedException();
		}

		$result = $item->save(
			['is_promoted' => false],
			['whitelist' => ['is_promoted'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully unpromoted.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to unpromote.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}
}

?>