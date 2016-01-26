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

use Exception;
use base_address\models\Addresses;
use base_address\models\Countries;
use base_core\extensions\cms\Settings;
use base_core\models\Locales;
use base_core\models\Timezones;
use base_core\models\Users;
use billing_core\models\Currencies;
use billing_core\models\TaxTypes;
use billing_invoice\models\Invoices;
use billing_payment\models\PaymentMethods;
use li3_flash_message\extensions\storage\FlashMessage;
use li3_mailer\action\Mailer;
use lithium\analysis\Logger;
use lithium\core\Environment;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\security\Auth;
use lithium\security\validation\FormSignature;

class UsersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\AdminActivateTrait;
	use \base_core\controllers\AdminLockTrait;

	public function admin_add() {
		extract(Message::aliases());

		$item = Users::create();

		if ($this->request->data) {
			$this->request->data['password'] = Users::hashPassword(
				$this->request->data['password']
			);
			$events = ['create', 'passwordInit'];

			if (!empty($this->request->data['reset_answer'])) {
				$events[] = 'answerInit';

				$this->request->data['reset_answer'] = Users::hashAnswer(
					$this->request->data['reset_answer']
				);
			}

			if ($item->save($this->request->data, compact('events'))) {
				FlashMessage::write($t('Successfully saved.', ['scope' => 'base_core']), [
					'level' => 'success'
				]);
				return $this->redirect(['action' => 'index', 'library' => 'base_core']);
			} else {
				FlashMessage::write($t('Failed to save.', ['scope' => 'base_core']), [
					'level' => 'error'
				]);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}

	public function admin_edit() {
		extract(Message::aliases());

		$item = Users::find($this->request->id);

		if ($this->request->data) {
			$events = ['update'];

			$protectedFields = [
				'password' => function(array $data) {
					return Users::hashPassword($data['password']);
				},
				'reset_answer' => function(array $data) {
					return Users::hashAnswer($data['reset_answer']);
				}
			];
			foreach ($protectedFields as $field => $value) {
				if ($this->request->data[$field]) {
					if ($field === 'password') {
						$events[] = 'passwordInit';
					}
					$this->request->data[$field] = $value($this->request->data);
				} else {
					unset($this->request->data[$field]);
				}
			}

			if ($item->save($this->request->data)) {
				FlashMessage::write($t('Successfully saved.', ['scope' => 'base_core']), [
					'level' => 'success'
				]);
				return $this->redirect(['action' => 'index', 'library' => 'base_core']);
			} else {
				FlashMessage::write($t('Failed to save.', ['scope' => 'base_core']), [
					'level' => 'error'
				]);
			}
		}
		$this->_render['template'] = 'admin_form';
		return compact('item') + $this->_selects($item);
	}

	protected function _selects($item = null) {
		extract(Message::aliases());

		$roles = Users::enum('role');
		$timezones = Timezones::find('list');
		$locales = Locales::find('list');

		if (class_exists('\base_address\models\Countries')) {
			$countries = Countries::find('list');
		} else {
			$countries = array_combine($list = explode(' ', PROJECT_COUNTRIES), $list);
		}

		if ($item) {
			if (Libraries::get('base_address')) {
				$addresses = [
					null => '-- ' . $t('no address', ['scope' => 'base_core']) . ' --'
				];
				$addresses += Addresses::find('list', [
					'conditions' => [
						'OR' => [
							['user_id' => null],
							['user_id' => $item->id]
						]
					]
				]);
			}
		}

		if ($useBilling = Libraries::get('billing_core')) {
			$currencies = Currencies::find('list');
			$taxTypes = TaxTypes::find('list');
		}
		if ($useBillingPayment = Libraries::get('billing_payment')) {
			$paymentMethods = PaymentMethods::find('list');
		}
		$useInvoice = Libraries::get('billing_invoice');
		$useEcommerce = Libraries::get('ecommerce_core');
		$useRent = Libraries::get('ecommerce_rent');

		if ($useAutoInvoice = $useInvoice && Settings::read('invoice.autoInvoice')) {
			$autoInvoiceFrequencies = Invoices::enum('frequency');
		}
		$useAutoPay = $useInvoice && Settings::read('invoice.autoPay');

		return compact(
			'roles',
			'timezones',
			'countries',
			'locales',

			// Optional
			'currencies',
			'addresses',
			'autoInvoiceFrequencies',
			'taxTypes',
			'paymentMethods',

			'useBilling',
			'useBillingPayment',
			'useInvoice',
			'useEcommerce',
			'useRent',
			'useAutoInvoice',
			'useAutoPay'
		);
	}

	// We don't need to check if current user is admin, as
	// anybody who can access the admin is an admin already.
	public function admin_change_role() {
		extract(Message::aliases());

		$item = Users::find('first', ['conditions' => ['id' => $this->request->id]]);
		$item->role = $this->request->role;

		if ($item->save(null, ['validate' => false, 'whitelist' => ['role']])) {
			FlashMessage::write($t("Assigned role `{:role}`.", ['scope' => 'base_core', 'role' => $item->role]), [
				'level' => 'success'
			]);
		} else {
			FlashMessage::write($t("Failed to assign role `{:role}`.", ['scope' => 'base_core', 'role' => $item->role]), [
				'level' => 'error'
			]);
		}
		$this->redirect($this->request->referer());
	}

	public function admin_session() {
		if (Auth::check('default')) {
			return $this->redirect('Pages::home');
		}
		$this->_render['layout'] = 'admin_blank';

		// Disable scripts to minimize attack surface.
		$this->set(['noScripts' => true]);
	}

	public function admin_login() {
		extract(Message::aliases());

		if (!$this->request->data) {
			throw new Exception('No data.');
		}
		if (!FormSignature::check($this->request)) {
			FlashMessage::write($t('Failed to authenticate. Please retry request.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		} elseif (Auth::check('default', $this->request)) {
			$message  = "Security: Authenticated user ";
			$message .= "with email `{$this->request->data['email']}`.";
			Logger::write('debug', $message);

			FlashMessage::write($t('Authenticated.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
			return $this->redirect('Pages::home');
		} else {
			FlashMessage::write($t('Failed to authenticate.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
		}

		$message  = "Security: Failed authentication for user ";
		$message .= "with email `{$this->request->data['email']}`. Delaying response.";
		Logger::write('debug', $message);


		// Naive implementation to conunterfeit brute forcing credentials.
		// FIXME Implement advanced throttling with rate-limiter on token bucket basis.
		// 5s as per https://www.owasp.org/index.php/Guide_to_Authentication
		sleep(5);

		return $this->redirect($this->request->referer());
	}

	public function admin_logout() {
		extract(Message::aliases());

		Auth::clear('default');

		FlashMessage::write($t('Successfully logged out.', ['scope' => 'base_core']), [
			'level' => 'success'
		]);
		return $this->redirect('Users::session');
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
		if (Settings::read('user.sendActivationMail') && $item->is_notified) {
			$result = $result && Mailer::deliver('user_activated', [
				'library' => 'billing_core',
				'to' => $item->email,
				'subject' => $t('Your account has been activated.', [
					'locale' => $item->locale,
					'scope' => 'base_core'
				]),
				'data' => [
					'user' => $item
				]
			]);
		}
		if ($result) {
			$model::pdo()->commit();

			Logger::write('debug', "Activated user `{$item->email}`.");
			FlashMessage::write($t('Activated.', ['scope' => 'base_core']), [
				'level' => 'success'
			]);
		} else {
			$model::pdo()->rollback();
			FlashMessage::write($t('Failed to activate.', ['scope' => 'base_core']), [
				'level' => 'error'
			]);
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
		FlashMessage::write($t('Became user `{:name}`.', ['scope' => 'base_core', 'name' => $new['name']]), [
			'level' => 'success'
		]);

		return $this->redirect($this->request->referer());
	}

	public function admin_debecome() {
		extract(Message::aliases());

		$auth = Auth::check('default');

		Auth::set('default', $auth['original']);
		FlashMessage::write($t('Became user `{:name}` again.', ['scope' => 'base_core', 'name' => $auth['original']['name']]), [
			'level' => 'success'
		]);

		return $this->redirect($this->request->referer());
	}
}

?>