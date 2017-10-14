<?php
/**
 * Copyright 2013 David Persson. All rights reserved.
 * Copyright 2016 Atelier Disko. All rights reserved.
 *
 * Use of this source code is governed by a BSD-style
 * license that can be found in the LICENSE file.
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