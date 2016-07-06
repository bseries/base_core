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
 * License. If not, see http://atelierdisko.de/licenses.
 */

namespace base_core\controllers;

use base_core\extensions\cms\Settings;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminUpdateStatusTrait {

	public function admin_update_status() {
		extract(Message::aliases());
		$user = Auth::check('default');

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::first($this->request->id);

		if ($model::hasBehavior('Ownable') && Settings::read('security.checkOwner')) {
			!Gate::checkRight('owner') && Gate::owned($item, ['require' => true]);
		}

		$result = $item->save(
			['status' => $this->request->status],
			['whitelist' => ['status'], 'validate' => false]
		);
		if ($result) {
			$model::pdo()->commit();

			FlashMessage::write($t('Successfully updated status.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();

			FlashMessage::write($t('Failed to update status.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		}
		return $this->redirect($this->request->referer());
	}
}

?>