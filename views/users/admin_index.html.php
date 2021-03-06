<?php

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
		<?= $this->html->link($t('user'), ['action' => 'add'], ['class' => 'button add']) ?>
	</div>

	<table>
		<thead>
			<tr>
				<td data-sort="is-active" class="flag table-sort"><?= $t('active?') ?>
				<td data-sort="is-locked" class="flag table-sort is-locked "><?= $t('locked?') ?>
				<td data-sort="is-notified" class="flag table-sort"><?= $t('notified?') ?>
				<?php if ($useRent): ?>
					<td data-sort="can-rent" class="flag table-sort"><?= $t('rent?') ?>
				<?php endif ?>
				<td class="media">
				<?php if ($useBilling): ?>
					<td data-sort="number" class="number table-sort"><?= $t('Number') ?>
				<?php endif ?>
				<td data-sort="name" class="name emphasize table-sort"><?= $t('Name') ?>
				<?php if (!$useBilling): ?>
					<td data-sort="email" class="email table-sort"><?= $t('Email') ?>
				<?php endif ?>
				<td data-sort="role" class="table-sort"><?= $t('Role') ?>
				<td data-sort="modified" class="date table-sort desc"><?= $t('Modified') ?>
				<?php if ($useSites): ?>
					<td data-sort="site" class="table-sort"><?= $t('Site') ?>
				<?php endif ?>
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
				<td class="flag"><i class="material-icons"><?= ($item->is_active ? 'done' : '') ?></i>
				<td class="flag"><i class="material-icons">
					<?php if (!$item->is_locked && $item->mustLock()): ?>
						lock_outline
					<?php else: ?>
						<?= ($item->is_locked ? 'lock ' : '') ?>
					<?php endif ?>
				</i>
				<td class="flag"><i class="material-icons"><?= ($item->is_notified ? 'done' : '') ?></i>
				<?php if ($useRent): ?>
					<td class="flag"><i class="material-icons"><?= ($item->can_rent ? 'done' : '') ?></i>
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
				<?php if ($useSites): ?>
					<td>
						<?= $item->site ?: '-' ?>
				<?php endif ?>
				<td class="actions">
					<?php if (!$item->is_locked || !$item->mustLock()): ?>
						<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), [
							'id' => $item->id, 'action' => $item->is_locked ? 'unlock' : 'lock'
						], ['class' => 'button']) ?>
					<?php endif ?>
					<?= $this->html->link($item->is_active ? $t('deactivate') : $t('activate'), [
						'id' => $item->id, 'action' => $item->is_active ? 'deactivate' : 'activate'
					], ['class' => 'button']) ?>
					<?php if (Settings::read('user.useBecome') && $authedUser->id != $item->id): ?>
						<?= $this->html->link($t('become'), ['id' => $item->id, 'action' => 'become', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php endif ?>
					<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>

	<?=$this->_render('element', 'paging', compact('paginator'), ['library' => 'base_core']) ?>

	<?php if (Settings::read('user.sendActivationMail')): ?>
	<div class="bottom-help">
		<?= $t('The user will be notified by e-mail when her account is activated.') ?>
	</div>
	<?php endif ?>
	<?php if (Settings::read('user.useBecome')): ?>
	<div class="bottom-help">
		<?= $t('You can temporarily use the identity of a user by clicking on the `become` button in the row of that user.') ?>
	</div>
	<?php endif ?>

</article>