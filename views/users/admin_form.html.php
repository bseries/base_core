<?php

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
<article>
	<?=$this->form->create($item) ?>
		<?php if ($item->exists()): ?>
			<?= $this->form->field('id', ['type' => 'hidden']) ?>
		<?php endif ?>

		<div class="grid-row">
			<section class="grid-column-left">
				<?= $this->form->field('name', [
					'type' => 'text',
					'label' => $t('Name'),
					'class' => 'use-for-title'
				]) ?>

				<?php if ($useBilling): ?>
					<?= $this->form->field('number', [
						'type' => 'text',
						'label' => $t('Number'),
						'placeholder' => $t('Leave empty to autogenerate number.')
					]) ?>
				<?php endif ?>
			</section>
			<section class="grid-column-right">
				<?= $this->form->field('email', [
					'type' => 'email',
					'label' => $t('E–mail'),
					'autocomplete' => 'off'
				]) ?>
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

			<div class="grid-column-left cred-fields">
				<?= $this->form->field('change_password', [
					'type' => 'checkbox',
					'label' => $t('change password'),
					'label' => !$item->exists() ? $t('provide a password') : $t('change password'),
					'checked' => !$item->exists(),
					'value' => 1,
					// force checkbox to initial state
					// https://developer.mozilla.org/en-US/docs/Web/Security/Securing_your_site/Turning_off_form_autocompletion
					'autocomplete' => 'new-password'
				]) ?>
				<?=$this->form->field('password', [
					'type' => 'password',
					'label' => $t('Password'),
					'autocomplete' => 'off',
					'disabled' => $item->exists(),
					'placeholder' => $t('Pick a strong password.'),
					'autocomplete' => 'new-password'
				]) ?>
				<?= $this->form->field('change_answer', [
					'type' => 'checkbox',
					'label' => !$item->exists() || !$item->answer ? $t('provide a reset answer') : $t('change reset answer'),
					'checked' => false,
					'value' => 1,
					'autocomplete' => 'off' // force checkbox to initial state
				]) ?>
				<?=$this->form->field('answer', [
					'type' => 'password',
					'label' => $t('Password reset answer'),
					'autocomplete' => 'off',
					'disabled' => true
				]) ?>
				<div class="help">
					<?= $t('Required to allow resetting password.') ?>
					<?= $t('This is an additional security measure to protect password resets.') ?>
				</div>

				<?=$this->form->field('auth_token', [
					'type' => 'text',
					'autocomplete' => 'off',
					'label' => $t('API Authentication token'),
					'placeholder' => $t('Keep empty if the user has no API access.')
				]) ?>
				<div class="help">
					<?= $t('A token that can be provided instead of the password.') ?>
					<?= $t('Only needed when this is a technical user with access to the API.') ?>
				</div>
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

					<?= $this->html->link($t('address'), [
						'library' => 'base_address',
						'controller' => 'Addresses', 'action' => 'add'
					], ['class' => 'button add']) ?>

					<?php if ($item->exists() && $item->billing_address_id): ?>
						<?= $this->html->link($t('open'), [
							'library' => 'base_address',
							'controller' => 'Addresses', 'action' => 'edit',
							'id' => $item->billing_address_id
						], ['class' => 'button']) ?>
					<?php endif ?>
				</section>
				<section class="grid-column-right">
					<?= $this->form->field('currency', [
						'type' => 'select',
						'label' => $t('Currency'),
						'list' => $currencies
					]) ?>
					<?= $this->form->field('vat_reg_no', [
						'type' => 'text',
						'label' => $t('VAT Reg. No.')
					]) ?>
					<?= $this->form->field('tax_no', [
						'type' => 'text',
						'label' => $t('Tax No.')
					]) ?>
					<?= $this->form->field('tax_type', [
						'type' => 'select',
						'label' => $t('Perferred tax type'),
						'list' => $taxTypes
					]) ?>

					<?php if ($useBillingPayment): ?>
						<?= $this->form->field('payment_method', [
							'type' => 'select',
							'label' => $t('Perferred payment method'),
							'list' => $paymentMethods
						]) ?>
					<?php endif ?>
					<?php if ($useAutoInvoice): ?>
						<?= $this->form->field('auto_invoice_frequency', [
							'type' => 'select',
							'label' => $t('Auto Invoice Frequency'),
							'list' => $autoInvoiceFrequencies
						]) ?>
						<?= $this->form->field('is_auto_invoiced', [
							'type' => 'checkbox',
							'label' => $t('auto invoice'),
							'checked' => (boolean) $item->is_auto_invoiced,
							'value' => 1
						]) ?>
					<?php endif ?>
				</section>
			</div>
		<?php endif ?>
		<?php if ($useEcommerce): ?>
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
				<?php if ($useRent): ?>
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
			<div class="bottom-actions__left">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('delete'), [
						'action' => 'delete', 'id' => $item->id
					], ['class' => 'button large delete']) ?>
				<?php endif ?>
			</div>
			<div class="bottom-actions__right">
				<?php if ($item->exists()): ?>
					<?= $this->html->link(
						$item->is_active ? $t('deactivate') : $t('activate'),
						['id' => $item->id, 'action' => $item->is_active ? 'deactivate' : 'activate'],
						['class' => 'button large']
					) ?>
					<?= $this->html->link($item->is_locked ? $t('unlock') : $t('lock'), [
						'id' => $item->id,
						'action' => $item->is_locked ? 'unlock' : 'lock'
					], [
						'class' => 'button large'
					]) ?>
				<?php endif ?>

				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>