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

trait AdminEditTrait {

	public function admin_edit() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$redirectUrl = $this->_redirectUrl + [
			'action' => 'index', 'library' => $this->_library
		];

		$item = $model::find($this->request->id);

		if ($this->request->data) {
			if ($item->save($this->request->data)) {
				$model::pdo()->commit();
				FlashMessage::write($t('Successfully saved.'), ['level' => 'success']);
				return $this->redirect($redirectUrl);
			} else {
				$model::pdo()->rollback();
				FlashMessage::write($t('Failed to save.'), ['level' => 'error']);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}
}

?>