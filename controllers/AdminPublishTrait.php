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

trait AdminPublishTrait {

	public function admin_publish() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($model::hasBehavior('Ownable') && Settings::read('security.checkOwner')) {
			!Gate::checkRight('owner') && Gate::owned($item, ['require' => true]);
		}

		$result = $item->save(
			['is_published' => true],
			['whitelist' => ['is_published'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();

			FlashMessage::write($t('Successfully published.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to publish.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		}
		return $this->redirect($this->request->referer());
	}

	public function admin_unpublish() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($model::hasBehavior('Ownable') && Settings::read('security.checkOwner')) {
			!Gate::checkRight('owner') && Gate::owned($item, ['require' => true]);
		}

		$result = $item->save(
			['is_published' => false],
			['whitelist' => ['is_published'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();

			FlashMessage::write($t('Successfully unpublished.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to unpublish.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		}
		return $this->redirect($this->request->referer());
	}
}

?>