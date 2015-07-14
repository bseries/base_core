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
use base_core\security\Gate;
use lithium\g11n\Message;
use li3_flash_message\extensions\storage\FlashMessage;
use base_core\models\Users;

trait AdminAddTrait {

	public function admin_add() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::create([
			// Will not be saved without error when there is no such field.
			'owner_id' => $user['id']
		]);

		if ($this->request->data) {
			if ($model::hasBehavior('Ownable')) {
				// Force current user if the current user doesn't have
				// the perms to change users.

				if (!Gate::check('users')) {
					$this->request->data['owner_id'] = $user['id'];
				}
				// Note: Explictly allow saving owner_id on ADD here.
			}
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
		$useOwner = Gate::check('users');

		$users = Users::find('list', ['order' => 'name']);

		$this->_render['template'] = 'admin_form';
		return compact('item', 'isTranslated', 'users', 'useOwner') + $this->_selects($item);
	}
}

?>