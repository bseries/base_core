<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use base_core\extensions\cms\Settings;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminDeleteTrait {

	public function admin_delete() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);

		if ($model::hasBehavior('Ownable') && Settings::read('security.checkOwner')) {
			!Gate::checkRight('owner') && Gate::owned($item, ['require' => true]);
		}

		if ($item->delete()) {
			$model::pdo()->commit();

			FlashMessage::write($t('Successfully deleted.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to delete.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
			return $this->redirect($this->request->referer());
		}
		return $this->redirect(['action' => 'index', 'library' => $this->_library]);
	}
}

?>