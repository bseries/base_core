<?php

use lithium\core\Libraries;

$this->set([
	'page' => [
		'type' => 'multiple',
		'object' => $t('virtual users')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?> use-list">

	<div class="top-actions">
		<?= $this->html->link($t('new virtual user'), ['action' => 'add', 'library' => 'base_core'], ['class' => 'button add']) ?>
	</div>

	<div class="help">
		<?= $t("Virtual users are users which you want to track and associated with other items (i.e. an order), These users didn't sign up directly but may have been creating a temporary account.") ?>
	</div>

	<table>
		<thead>
			<tr>
				<td data-sort="is-active" class="is-active flag list-sort"><?= $t('Active?') ?>
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
					<?= $this->html->link($t('open'), ['id' => $item->id, 'action' => 'edit', 'library' => 'base_core'], ['class' => 'button']) ?>
			<?php endforeach ?>
		</tbody>
	</table>

</article>