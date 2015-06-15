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

trait AdminEditTrait {

	public function admin_edit() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);

		if ($user['role'] !== 'admin' && !$item->isOwner($user)) {
			throw new AccessDeniedException();
		}

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				$model::pdo()->commit();

				FlashMessage::write($t('Successfully saved.', ['scope' => 'base_core']), [
					'level' => 'success'
				]);
				return $this->redirect(['action' => 'index', 'library' => $this->_library]);
			} else {
				$model::pdo()->rollback();

				FlashMessage::write($t('Failed to save.', ['scope' => 'base_core']), [
					'level' => 'error'
				]);
			}
		}
		$isTranslated = $model::hasBehavior('Translatable');

		$this->_render['template'] = 'admin_form';
		return compact('item', 'isTranslated') + $this->_selects($item);
	}
}

?>