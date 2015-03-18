<?php

use lithium\core\Libraries;
use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('users')
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
		<?= $this->html->link($t('new user'), ['action' => 'add'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
		<?php if (Settings::read('user.sendActivationMail')): ?>
			<?= $t('The user will be notified by e-mail when her account is activated.') ?>
		<?php endif ?>
		<?= $t('You can temporarily use the identity of a user by clicking on the `become` button in the row of that user.') ?>
	</div>

	<table>
		<thead>
			<tr>
				<td data-sort="is-active" class="flag table-sort"><?= $t('Active?') ?>
				<td data-sort="is-notified" class="flag table-sort"><?= $t('Notified?') ?>
				<?php if ($useBilling = Libraries::get('billing_core')): ?>
					<td data-sort="is-auto-invoiced" class="flag table-sort"><?= $t('Auto inv.?') ?>
				<?php endif ?>
				<?php if ($useRent = Libraries::get('ecommerce_rent')): ?>
					<td data-sort="can-rent" class="flag table-sort"><?= $t('Rent?') ?>
				<?php endif ?>
				<td>
				<?php if ($useBilling): ?>
					<td data-sort="number" class="number table-sort"><?= $t('Number') ?>
				<?php endif ?>
				<td data-sort="name" class="name emphasize table-sort"><?= $t('Name') ?>
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
				<td class="flag"><?= $item->is_notified ? '✓ ' : '×' ?>
				<?php if ($useBilling): ?>
					<td class="flag"><?= $item->is_auto_invoiced ? '✓ ' : '×' ?>
				<?php endif ?>
				<?php if ($useRent): ?>
					<td class="flag"><?= $item->can_rent ? '✓ ' : '×' ?>
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
					<?php if ($authedUser->id != $item->id): ?>
						<?= $this->html->link($t('become'), ['id' => $item->id, 'action' => 'become', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php endif ?>
					<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>

	<?=$this->view()->render(['element' => 'paging'], compact('paginator'), ['library' => 'base_core']) ?>
</article>