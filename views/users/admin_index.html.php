<?php

use lithium\core\Libraries;

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('users')
	]
]);

$nickRgb = function($nick) {
	$hash = abs(crc32($nick));

	$rgb = [$hash % 255, $hash % 255, $hash % 255];
	$rgb[$hash % 2] = 100;

	return $rgb;
};


?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">

	<div class="top-actions">
		<?= $this->html->link($t('new user'), ['action' => 'add', 'library' => 'base_core'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
	<?php if (Features::enabled('user.sendActivationMail')): ?>
		<?= $t('The user will be notified by e-mail when her account is activated.') ?>
	<?php endif ?>
		<?= $t('You can temporarily use the identity of a user by clicking on the `become` button in the row of that user.') ?>
	</div>

	<table>
		<thead>
			<tr>
				<td data-sort="is-active" class="is-active flag list-sort"><?= $t('Active?') ?>
				<td data-sort="is-notified" class="is-notified flag list-sort"><?= $t('Notified?') ?>
				<td>
				<?php if ($useBilling = Libraries::get('billing_core')): ?>
					<td data-sort="number" class="number list-sort"><?= $t('Number') ?>
				<?php endif ?>
				<td data-sort="name" class="name emphasize list-sort asc"><?= $t('Name') ?>
				<td data-sort="email" class="email list-sort"><?= $t('Email') ?>
				<td data-sort="role" class="role list-sort"><?= $t('Role') ?>
				<td data-sort="created" class="date created list-sort"><?= $t('Created') ?>
				<td class="actions">
					<?= $this->form->field('search', [
						'type' => 'search',
						'label' => false,
						'placeholder' => $t('Filter'),
						'class' => 'list-search'
					]) ?>
		</thead>
		<tbody class="list">
			<?php foreach ($data as $item): ?>
			<tr>
				<td class="is-active flag"><?= $item->is_active ? '✓ ' : '×' ?>
				<td class="is-notified flag"><?= $item->is_notified ? '✓ ' : '×' ?>
				<td>
					<div
						class="avatar"
						style="background: rgb(<?=implode(',' , $nickRgb($item->email))?>);"
					>
					</div>
				<?php if ($useBilling): ?>
					<td class="number emphasize"><?= $item->number ?>
				<?php endif ?>
				<td class="name emphasize"><?= $item->name ?>
				<td class="email"><?= $item->email ?>
				<td class="role"><?= $item->role ?>
				<td class="date created">
					<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
						<?= $this->date->format($item->created, 'date') ?>
					</time>
				<td class="actions">
					<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php if ($item->is_active): ?>
						<?= $this->html->link($t('deactivate'), ['id' => $item->id, 'action' => 'deactivate', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php else: ?>
						<?= $this->html->link($t('activate'), ['id' => $item->id, 'action' => 'activate', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php endif ?>
					<?php if ($authedUser['id'] != $item->id): ?>
						<?= $this->html->link($t('become'), ['id' => $item->id, 'action' => 'become', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?php endif ?>
					<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>
</article>