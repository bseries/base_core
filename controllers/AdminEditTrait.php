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
use base_core\models\Sites;
use base_core\models\Users;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminEditTrait {

	public function admin_edit() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::find($this->request->id);
		$whitelist = null;

		if ($model::hasBehavior('Ownable')) {
			if (!Gate::checkRight('owner')) {
				if (Settings::read('security.checkOwner') && !Gate::owned($item)) {
					throw new AccessDeniedException();
				}
				// Prevent saving user data, only admins can do that.
				$whitelist = array_diff(array_keys($model::schema()->fields()), [
					'owner_id'
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

		$useOwner = Settings::read('security.checkOwner') && $model::hasBehavior('Ownable');
		$useOwner = $useOwner && Gate::checkRight('owner');
		if ($useOwner) {
			$users = Users::find('list', [
				'order' => 'name',
				'conditions' => ['is_active' => true]
			]);
		}

		if ($useSites = Settings::read('useSites')) {
			$sites = Sites::find('list');
		}

		$this->_render['template'] = 'admin_form';
		return compact(
			'item', 'users',
			'isTranslated', 'useOwner',
			'useSites', 'sites'
		) + $this->_selects($item);
	}
}

?>