<?php
/**
 * Base Core
 *
 * Copyright (c) 2013 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use lithium\core\Libraries;
use lithium\g11n\Message;
use lithium\security\Auth;
use li3_flash_message\extensions\storage\FlashMessage;

use base_core\models\VirtualUsers;
use base_address\models\Addresses;
use billing_invoice\models\Invoices;

use base_core\models\Locales;
use base_core\models\Timezones;
use billing_core\models\Currencies;
use base_address\models\Countries;

class VirtualUsersController extends \base_core\controllers\BaseController {

	use \base_core\controllers\AdminIndexTrait;
	use \base_core\controllers\AdminDeleteTrait;
	use \base_core\controllers\AdminActivateTrait;
	use \base_core\controllers\AdminAddTrait;
	use \base_core\controllers\AdminEditTrait;

	protected function _selects($item = null) {
		extract(Message::aliases());

		$roles = VirtualUsers::enum('role');
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
						'user_id' => $item->id
					]
				]);
			}
		}

		if (Libraries::get('billing_core')) {
			$currencies = Currencies::find('list');
		}
		if (Libraries::get('billing_invoice')) {
			$invoiceFrequencies = Invoices::enum('frequency');
		}

		return compact(
			'roles',
			'timezones',
			'countries',
			'locales',

			// Optional
			'currencies',
			'addresses',
			'invoiceFrequencies'
		);
	}
}

?>