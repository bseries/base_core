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
use base_core\base\Sites;
use base_core\models\Users;
use base_core\security\Gate;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;

trait AdminAddTrait {

	public function admin_add() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();

		$item = $model::create([
			// Will not be saved without error when there is no such field.
			'owner_id' => Gate::user(true, 'id')
		]);

		if ($this->request->data) {
			if ($model::hasBehavior('Ownable')) {
				// Force current user if the current user doesn't have
				// the perms to change users.

				if (!Gate::checkRight('owner')) {
					$this->request->data['owner_id'] = Gate::user(true, 'id');
				}
				// Note: Explictly allow saving owner_id on ADD here.
			}
			if ($item->save($this->request->data)) {
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
			$sites = Sites::enum();
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