<?php
/**
 * Base
 *
 * Copyright (c) 2013-2014 Atelier Disko - All rights reserved.
 *
 * This software is proprietary and confidential. Redistribution
 * not permitted. Unless required by applicable law or agreed to
 * in writing, software distributed on an "AS IS" BASIS, WITHOUT-
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */

namespace base_core\controllers;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

trait AdminIndexTrait {

	public function admin_index() {
		$model = $this->_model;

		// Handle sorting. We support sorting by one
		// dimension at a time only.
		$order = ['modified' => 'DESC'];

		// Handle pagination.
		$page = $this->request->page ?: 1;
		Paginator::setDefaultItemCountPerPage($perPage = 20);
		Paginator::setDefaultScrollingStyle('Sliding');

		$count = $model::find('count');

		$paginator = new Paginator(new ArrayAdapter(
			range(0, $count)
		));
		$paginator->setCurrentPageNumber($page);
		$paginator->setCacheEnabled(false);

		$data = $model::find('all', [
			'page' => $page,
			'limit' => $perPage,
			'order' => $order
		]);

		return compact('data', 'paginator') + $this->_selects();
	}
}

?>