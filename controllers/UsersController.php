<?php
/**
 * Base Core
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use base_core\models\Users;
use base_core\models\Addresses;
use base_core\models\Currencies;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\g11n\Message;
use lithium\security\Auth;
use base_core\extensions\cms\Features;
use li3_mailer\action\Mailer;
use lithium\analysis\Logger;

class UsersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\AdminActivateTrait;

	public function admin_index() {
		$data = Users::find('all', [
			'order' => ['name' => 'ASC']
		]);
		return compact('data');
	}

	public function admin_add() {
		extract(Message::aliases());

		$item = Users::create();

		if ($this->request->data) {
			$this->request->data['password'] = Users::hashPassword(
				$this->request->data['password']
			);
			$events = ['create', 'passwordInit'];

			if ($item->save($this->request->data, compact('events'))) {
				FlashMessage::write($t('Successfully saved.'), ['level' => 'success']);
				return $this->redirect(['action' => 'index', 'library' => 'base_core']);
			} else {
				FlashMessage::write($t('Failed to save.'), ['level' => 'error']);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}

	public function admin_edit() {
		extract(Message::aliases());

		$item = Users::find($this->request->id);

		if ($this->request->data) {
			$events = ['create'];

			if ($this->request->data['password']) {
				$events[] = 'passwordChange';

				$this->request->data['password'] = Users::hashPassword(
					$this->request->data['password']
				);
			} else {
				unset($this->request->data['password']);
			}

			if ($item->save($this->request->data)) {
				FlashMessage::write($t('Successfully saved.'), ['level' => 'success']);
				return $this->redirect(['action' => 'index', 'library' => 'base_core']);
			} else {
				FlashMessage::write($t('Failed to save.'), ['level' => 'error']);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}

	protected function _selects($item = null) {
		extract(Message::aliases());

		$roles = Users::enum('role');
		$timezones = [
			'Europe/Berlin' => 'Europe/Berlin',
			'UTC' => 'UTC'
		];
		$currencies = Currencies::find('list');
		$locales = [
			'de' => 'Deutsch',
			'en' => 'English'
		];
		if ($item) {
			$addresses = [
				null => '-- ' . $t('no address') . ' --'
			];
			$addresses += Addresses::find('list', [
				'conditions' => [
					'user_id' => $item->id
				]
			]);
		}
		return compact('roles', 'timezones', 'currencies', 'locales', 'addresses');
	}

	// We don't need to check if current user is admin, as
	// anybody who can access the admin is an admin already.
	public function admin_change_role() {
		extract(Message::aliases());

		$item = Users::find('first', ['conditions' => ['id' => $this->request->id]]);
		$item->role = $this->request->role;

		if ($item->save(null, ['validate' => false, 'whitelist' => ['role']])) {
			FlashMessage::write($t("Assigned role `{$item->role}`."), ['level' => 'success']);
		} else {
			FlashMessage::write($t("Failed to assign role `{$item->role}`."), ['level' => 'error']);
		}
		$this->redirect($this->request->referer());
	}

	public function admin_session() {
		if (Auth::check('default')) {
			return $this->redirect('/admin');
		}
		$this->_render['layout'] = 'admin_blank';
	}

	public function admin_login() {
		extract(Message::aliases());

		if ($this->request->data) {
			if (Auth::check('default', $this->request)) {
				$message = "Authenticated user with email `{$this->request->data['email']}`.";
				Logger::write('debug', $message);
				FlashMessage::write($t('Authenticated.'), ['level' => 'success']);

				return $this->redirect('/admin');
			}
			$message = "Failed authentication for user with email `{$this->request->data['email']}`.";
			Logger::write('debug', $message);
			FlashMessage::write($t('Failed to authenticate.'), ['level' => 'error']);

			return $this->redirect($this->request->referer());
		}
	}

	public function admin_logout() {
		extract(Message::aliases());

		Auth::clear('default');

		FlashMessage::write($t('Successfully logged out.'), ['level' => 'success']);
		return $this->redirect('/admin/session');
	}

	// Overridden from trait.
	public function admin_activate() {
		extract(Message::aliases());

		$model = $this->_model;
		$model::pdo()->beginTransaction();
		$item = $model::first($this->request->id);

		$result = $item->save(
			['is_active' => true],
			['whitelist' => ['is_active'], 'validate' => false]
		);
		if (Features::read('user.sendActivationMail') && $item->is_notified) {
			$result = $result && Mailer::deliver('user_activated', [
				'to' => $item->email,
				'subject' => $t('Your account has been activated.'),
				'data' => [
					'user' => $item
				]
			]);
		}
		if ($result) {
			$model::pdo()->commit();

			Logger::write('debug', "Activated user `{$item->email}`.");
			FlashMessage::write($t('Activated.'), ['level' => 'success']);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to activate.'), ['level' => 'error']);
		}
		return $this->redirect($this->request->referer());
	}

	public function admin_become() {
		extract(Message::aliases());

		$auth = Auth::check('default');

		$new = Users::find('first', ['conditions' => ['id' => $this->request->id]])->data();

		if (isset($auth['original'])) {
			// If we already became another user keep original.
			$new['original'] = Users::find('first', ['conditions' => ['id' => $auth['original']['id']]])->data();
		} else {
			$new['original'] = Users::find('first', ['conditions' => ['id' => $auth['id']]])->data();
		}
		unset($new['password']);
		unset($new['original']['password']);

		Auth::set('default', $new);
		FlashMessage::write($t('Became user `{:name}`.', $new), ['level' => 'success']);

		return $this->redirect($this->request->referer());
	}

	public function admin_debecome() {
		extract(Message::aliases());

		$auth = Auth::check('default');

		Auth::set('default', $auth['original']);
		FlashMessage::write($t('Became user `{:name}` again.', $auth['original']), ['level' => 'success']);

		return $this->redirect($this->request->referer());
	}
}

?>