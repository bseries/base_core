<?php

use lithium\core\Libraries;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('virtual users')
	]
]);

$nickRgb = function($nick) {
	$hash = abs(crc32($nick)) * 2;

	$rgb = [$hash % 255, $hash % 255, $hash % 255];
	$rgb[$hash % 2] = 100;

	return $rgb;
};

?>
<article
	class="use-rich-index"
	data-endpoint="<?= $this->url([
		'action' => 'index',
		'page' => '__PAGE__',
		'orderField' => '__ORDER_FIELD__',
		'orderDirection' => '__ORDER_DIRECTION__',
		'filter' => '__FILTER__'
	]) ?>"
>

	<div class="top-actions">
		<?= $this->html->link($t('new virtual user'), ['action' => 'add', 'library' => 'base_core'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
		<?= $t("Virtual users are users which you want to track and associated with other items (i.e. an order), These users didn't sign up directly but may have been creating a temporary account.") ?>
	</div>

	<table>
		<thead>
			<tr>
				<td data-sort="is-active" class="flag table-sort"><?= $t('Active?') ?>
				<?php if ($useBilling = Libraries::get('billing_core')): ?>
					<td data-sort="is-auto-invoiced" class="flag table-sort"><?= $t('Auto inv.?') ?>
				<?php endif ?>
					<td class="media">
				<?php if ($useBilling): ?>
					<td data-sort="number" class="number table-sort"><?= $t('Number') ?>
				<?php endif ?>
				<td data-sort="name" class="name emphasize table-sort asc"><?= $t('Name') ?>
				<?php if (!$useBilling): ?>
					<td data-sort="email" class="email table-sort"><?= $t('Email') ?>
				<?php endif ?>
				<td data-sort="role" class="table-sort"><?= $t('Role') ?>
				<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
				<td class="actions">
					<?= $this->form->field('search', [
						'type' => 'search',
						'label' => false,
						'placeholder' => $t('Filter'),
						'class' => 'table-search',
						'value' => $this->_request->filter
					]) ?>
		</thead>
		<tbody class="list">
			<?php foreach ($data as $item): ?>
			<tr>
				<td class="flag"><?= $item->is_active ? '✓ ' : '×' ?>
				<?php if ($useBilling): ?>
					<td class="flag"><?= $item->is_auto_invoiced ? '✓ ' : '×' ?>
				<?php endif ?>
				<td class="media">
					<div
						class="avatar"
						style="background: rgb(<?=implode(',' , $nickRgb($item->email))?>);"
					>
					</div>
				<?php if ($useBilling): ?>
					<td class="number emphasize"><?= $item->number ?>
				<?php endif ?>
				<td class="name emphasize"><?= $item->name ?>
				<?php if (!$useBilling): ?>
					<td class="email"><?= $item->email ?>
				<?php endif ?>
				<td><?= $item->role ?>
				<td class="date">
					<time datetime="<?= $this->date->format($item->modified, 'w3c') ?>">
						<?= $this->date->format($item->modified, 'date') ?>
					</time>
				<td class="actions">
					<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'base_core'], ['class' => 'button delete']) ?>
					<?php if ($item->is_active): ?>
						<?= $this->html->link($t('deactivate'), ['id' => $item->id, 'action' => 'deactivate', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php else: ?>
						<?= $this->html->link($t('activate'), ['id' => $item->id, 'action' => 'activate', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php endif ?>
					<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>

</article>