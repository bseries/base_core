<?php

use lithium\core\Libraries;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->name,
		'empty' => $t('unnamed'),
		'object' => $t('user')
	],
	'meta' => [
		'is_active' => $item->is_active ? $t('activated') : $t('deactivated'),
		'is_locked' => $item->is_locked ? $t('locked') : null
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<div class="grid-row">
			<section class="grid-column-left">
				<?= $this->form->field('name', ['type' => 'text', 'label' => $t('Name'), 'class' => 'use-for-title']) ?>

				<?= $this->form->field('uuid', [
					'type' => 'text',
					'label' => $t('UUID'),
					'disabled' => true
				]) ?>

				<?php if ($useBilling = Libraries::get('billing_core')): ?>
					<?= $this->form->field('number', [
						'type' => 'text',
						'label' => $t('Number')
					]) ?>
					<div class="help"><?= $t('Leave empty to autogenerate number.') ?></div>
				<?php endif ?>
			</section>
			<section class="grid-column-right">
				<?= $this->form->field('email', ['type' => 'email', 'label' => $t('E–mail')]) ?>
				<?= $this->form->field('is_notified', [
					'type' => 'checkbox',
					'label' => $t('receives notifications'),
					'checked' => (boolean) $item->is_notified,
					'value' => 1
				]) ?>
			</section>
		</div>
		<div class="grid-row">
			<section class="grid-column-left">
			</section>
			<section class="grid-column-right">
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
				<?= $this->form->field('country', [
					'type' => 'select',
					'label' => $t('Country'),
					'list' => $countries
				]) ?>
			</section>
		</div>
		<div class="grid-row">
			<h1 class="h-beta"><?= $t('Security') ?></h1>

			<div class="grid-column-left">
				<?=$this->form->field('password', [
					'type' => 'password',
					'label' => $t('New password'),
					'autocomplete' => 'off'
				]) ?>
				<div class="help">
					<?= $t('Keep empty to leave password unchanged.') ?>
				</div>
				<?=$this->form->field('auth_token', [
					'type' => 'text',
					'label' => $t('Authentication token')
				]) ?>
				<?=$this->form->field('reset_answer', [
					'type' => 'password',
					'label' => $t('New reset answer'),
					'autocomplete' => 'off'
				]) ?>
				<div class="help">
					<?= $t('Keep empty to leave answer unchanged.') ?>
				</div>
				<?=$this->form->field('reset_token', [
					'type' => 'text',
					'label' => $t('Reset token')
				]) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('role', [
					'type' => 'select',
					'label' => $t('Role'),
					'list' => $roles
				]) ?>
			</div>
		</div>
		<?php if ($useBilling): ?>
			<div class="grid-row">
				<h1 class="h-beta"><?= $t('Billing') ?></h1>

				<section class="grid-column-left">
					<?= $this->form->field('billing_address_id', [
						'type' => 'select',
						'label' => $t('Billing Address'),
						'list' => $addresses
					]) ?>
					<div class="help">
						<?= $this->html->link($t('Create new address.'), [
							'library' => 'base_address',
							'controller' => 'Addresses', 'action' => 'add'
						]) ?>
					</div>
				</section>
				<section class="grid-column-right">
					<?= $this->form->field('currency', [
						'type' => 'select',
						'label' => $t('Currency'),
						'list' => $currencies
					]) ?>
					<?= $this->form->field('vat_reg_no', [
						'type' => 'text',
						'autocomplete' => 'off',
						'label' => $t('VAT Reg. No.')
					]) ?>
					<?= $this->form->field('auto_invoice_frequency', [
						'type' => 'select',
						'label' => $t('Auto Invoice Frequency'),
						'list' => $invoiceFrequencies
					]) ?>
					<?= $this->form->field('is_auto_invoiced', [
						'type' => 'checkbox',
						'label' => $t('auto invoice'),
						'checked' => (boolean) $item->is_auto_invoiced,
						'value' => 1
					]) ?>
				</section>
			</div>
		<?php endif ?>
		<?php if ($useEcommerce = Libraries::get('ecommerce_core')): ?>
			<div class="grid-row">
				<h1 class="h-beta"><?= $t('eCommerce') ?></h1>

				<section class="grid-column-left">
					<?= $this->form->field('shipping_address_id', [
						'type' => 'select',
						'label' => $t('Shipping Address'),
						'list' => $addresses
					]) ?>
					<div class="help">
						<?= $this->html->link($t('Create new address.'), [
							'library' => 'base_address',
							'controller' => 'Addresses', 'action' => 'add'
						]) ?>
					</div>
				</section>
				<?php if ($useRent = Libraries::get('ecommerce_rent')): ?>
					<section class="grid-column-right">
						<?= $this->form->field('can_rent', [
							'type' => 'checkbox',
							'label' => $t('can rent'),
							'checked' => (boolean) $item->can_rent,
							'value' => 1
						]) ?>
					</section>
				<?php endif ?>
			</div>
		<?php endif ?>
		<div class="bottom-actions">
			<?php if ($item->exists()): ?>
				<?= $this->html->link($item->is_active ? $t('deactivate') : $t('activate'), [
					'id' => $item->id,
					'action' => $item->is_active ? 'deactivate' : 'activate'
				], [
					'class' => 'button large'
				]) ?>
				<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), [
					'id' => $item->id,
					'action' => $item->is_locked ? 'unlock' : 'lock'
				], [
					'class' => 'button large'
				]) ?>
			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>