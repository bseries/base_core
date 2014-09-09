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

use base_core\models\VirtualUsers;
use base_core\models\Currencies;
use base_core\models\Addresses;
use billing_core\models\Invoices;
use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\security\Auth;
use li3_flash_message\extensions\storage\FlashMessage;

class VirtualUsersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\AdminActivateTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;

	public function admin_index() {
		$data = VirtualUsers::find('all', [
			'order' => ['name' => 'ASC']
		]);
		return compact('data');
	}

	protected function _selects($item = null) {
		extract(Message::aliases());

		$roles = VirtualUsers::enum('role');
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
					'virtual_user_id' => $item->id
				]
			]);
		}
		if (Libraries::get('billing_time')) {
			$invoiceFrequencies = Invoices::enum('frequency');
		}
		return compact('roles', 'timezones', 'currencies', 'locales', 'addresses', 'invoiceFrequencies');
	}
}

?>