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

trait AdminIndexTrait {

	public function admin_index() {
		$model = $this->_model;

		// Handle pagination.
		$page = $this->request->page ?: 1;
		$perPage = 20;

		// Handle sorting. We support sorting by one
		// dimension at a time only.
		$order = ['modified' => 'DESC'];

		$data = $model::find('all', [
			'page' => $page,
			'limit' => $perPage,
			'order' => $order
		]);

		$totalPages = ceil($model::find('count') / $perPage);

		$paging = [
			'current' => $page,
			'total' => $totalPages
		];

		return compact('data', 'paging') + $this->_selects();
	}
}

?>