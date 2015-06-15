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
use base_core\extensions\net\http\NotFoundException;

trait AdminEditTrait {

	public function admin_edit() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$query = [
			'conditions' => [
				'id' => $this->request->id,
			]
		];

		// Security: Implicitly check for ownership.
		if ($model::hasField('user_id') && $user['role'] !== 'admin') {
			$query['conditions']['user_id'] = $user['id'];

			var_dump($model::meta());die;
		}

		if (!($item = $model::find('first', $query))) {
			throw new NotFoundException();
		}

		$redirectUrl = $this->_redirectUrl($item) + [
			'action' => 'index', 'library' => $this->_library
		];

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				$model::pdo()->commit();

				FlashMessage::write($t('Successfully saved.', ['scope' => 'base_core']), [
					'level' => 'success'
				]);
				return $this->redirect($redirectUrl);
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