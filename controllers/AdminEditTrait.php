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
use base_core\base\Sites;
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

		$autoNumber = false;
		$nextNumber = null;
		if ($model::hasBehavior('ReferenceNumber')) {
			$autoNumber = (boolean) $model::behavior('ReferenceNumber')->config('generate');
			$nextNumber = $model::nextReferenceNumber($item);
		}

		$isTranslated = $model::hasBehavior('Translatable');

		$useOwner = Settings::read('security.checkOwner') && $model::hasBehavior('Ownable');
		$useOwner = $useOwner && Gate::checkRight('owner');
		if ($useOwner) {
			$users = $this->_users($item, ['field' => 'owner_id']);
		}

		if ($useSites = Settings::read('useSites')) {
			$sites = Sites::enum();
		}

		$this->_render['template'] = 'admin_form';
		return compact(
			'item',
			'users',
			'isTranslated',
			'autoNumber', 'nextNumber',
			'useOwner',
			'useSites', 'sites'
		) + $this->_selects($item);
	}
}

?>