<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * Licensed under the AD General Software License v1.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *
 * You should have received a copy of the AD General Software
 * License. If not, see https://atelierdisko.de/licenses.
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