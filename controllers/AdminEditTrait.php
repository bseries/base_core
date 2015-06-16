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
use base_core\models\Users;

trait AdminEditTrait {

	public function admin_edit() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);
		$whitelist = null;

		if ($model::hasBehavior('Ownable')) {
			if (!Gate::check('users')) {
				if (!Gate::owned($item)) {
					throw new AccessDeniedException();
				}
				// Prevent saving user data, only admins can do that.
				$whitelist = array_diff(array_keys($model::schema()->fields()), [
					'user_id', 'virtual_user_id'
				]);
			}
		}

		if ($this->request->data) {
			if ($item->save($this->request->data, compact('whitelist'))) {
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
		$users = Users::find('list', ['order' => 'name']);
		$useOwner = Gate::check('users');

		$this->_render['template'] = 'admin_form';
		return compact('item', 'isTranslated', 'users', 'useOwner') + $this->_selects($item);
	}
}

?>