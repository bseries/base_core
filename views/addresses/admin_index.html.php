<?php

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('addresses')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">

	<div class="top-actions">
		<?= $this->html->link($t('new address'), ['action' => 'add', 'library' => 'base_core'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
		<?= $t("Addresses can be owned by a user but addresses without an owner are possible, too.") ?>
	</div>

	<?php if ($data->count()): ?>
	<table>
		<thead>
			<tr>
				<td data-sort="user" class="user list-sort"><?= $t('User') ?>
				<td data-sort="address" class="emphasize address list-sort"><?= $t('Address') ?>
				<td data-sort="created" class="date created list-sort desc"><?= $t('Created') ?>
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
				<?php $user = $item->user() ?>
			<tr>
				<td class="user">
				<?php if ($user): ?>
					<?= $this->html->link($user->title(), [
						'controller' => $user->isVirtual() ? 'VirtualUsers' : 'Users',
						'action' => 'edit', 'id' => $user->id,
						'library' => 'base_core'
					]) ?>
				<?php else: ?>
					-
				<?php endif ?>
				<td class="emphasize address"><?= $item->format('oneline') ?>
				<td class="date created">
					<time datetime="<?= $this->date->format($item->created, 'w3c') ?>">
						<?= $this->date->format($item->created, 'date') ?>
					</time>
				<td class="actions">
					<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'base_core'], ['class' => 'button']) ?>
					<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php else: ?>
		<div class="none-available"><?= $t('No items available, yet.') ?></div>
	<?php endif ?>
</article>