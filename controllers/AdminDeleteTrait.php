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

trait AdminDeleteTrait {

	public function admin_delete() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);

		if ($item->delete()) {
			$model::pdo()->commit();
			FlashMessage::write($t('Successfully deleted.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to delete.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}
}

?>