<?php

use lithium\core\Libraries;

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->name,
		'empty' => $t('unnamed'),
		'object' => $t('virtual user')
	],
	'meta' => [
		'is_active' => $item->is_active ? $t('activated') : $t('deactivated')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<div class="grid-row<?= ($useBilling = Libraries::get('billing_core')) ? '' : ' grid-row-last'?>">
			<section class="grid-column-left">
				<?= $this->form->field('name', ['type' => 'text', 'label' => $t('Name'), 'class' => 'use-for-title']) ?>
				<?php if ($useBilling): ?>
					<?= $this->form->field('number', [
						'type' => 'text',
						'label' => $t('Number')
					]) ?>
					<div class="help"><?= $t('Leave empty to autogenerate number.') ?></div>
				<?php endif ?>
				<?= $this->form->field('email', ['type' => 'email', 'label' => $t('E–mail')]) ?>
				<?= $this->form->field('is_notified', [
					'type' => 'checkbox',
					'label' => $t('receives notifications'),
					'checked' => (boolean) $item->is_notified
				]) ?>
			</section>
			<section class="grid-column-right">
				<?= $this->form->field('role', [
					'type' => 'select',
					'label' => $t('Role'),
					'list' => $roles
				]) ?>
				<?= $this->form->field('locale', [
					'type' => 'select',
					'label' => $t('Locale'),
					'list' => $locales
				]) ?>
				<?= $this->form->field('timezone', [
					'type' => 'select',
					'label' => $t('Timezone'),
					'list' => $timezones
				]) ?>
			</section>
		</div>
		<?php if ($useBilling): ?>
			<div class="grid-row-last">
				<section class="grid-column-left">
					<?= $this->form->field('billing_currency', [
						'type' => 'select',
						'label' => $t('Billing Currency'),
						'list' => $currencies
					]) ?>
					<div class="help">
						<?= $this->html->link($t('Create new address.'), ['controller' => 'Addresses', 'action' => 'add', 'library' => 'base_core']) ?>
					</div>
					<?= $this->form->field('billing_vat_reg_no', [
						'type' => 'text',
						'autocomplete' => 'off',
						'label' => $t('Billing VAT Reg. No.')
					]) ?>
					<div class="help">
						<?= $this->html->link($t('Create new address.'), ['controller' => 'Addresses', 'action' => 'add', 'library' => 'base_core']) ?>
					</div>
				</section>
				<section class="grid-column-right">
					<?= $this->form->field('billing_address_id', [
						'type' => 'select',
						'label' => $t('Billing Address'),
						'list' => $addresses
					]) ?>
					<?= $this->form->field('shipping_address_id', [
						'type' => 'select',
						'label' => $t('Shipping Address'),
						'list' => $addresses
					]) ?>
				</section>
			</div>
		<?php endif ?>
		<div class="bottom-actions">
			<?php if ($item->exists()): ?>
				<?php if ($item->is_active): ?>
					<?= $this->html->link($t('deactivate'), ['id' => $item->id, 'action' => 'deactivate', 'library' => 'base_core'], ['class' => 'button large']) ?>
				<?php else: ?>
					<?= $this->html->link($t('activate'), ['id' => $item->id, 'action' => 'activate', 'library' => 'base_core'], ['class' => 'button large']) ?>
				<?php endif ?>
			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>