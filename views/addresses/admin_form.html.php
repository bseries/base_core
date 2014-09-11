<?php

$this->set([
	'page' => [
		'type' => 'single',
		'title' => false,
		'empty' => false,
		'object' => $t('address')
	]
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<?=$this->form->create($item) ?>
		<?= $this->form->field('id', ['type' => 'hidden']) ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<div class="compound-users">
					<?php
						$user = $item->exists() ? $item->user() : false;
					?>
					<?= $this->form->field('user_id', [
						'type' => 'select',
						'label' => $t('User'),
						'list' => $users,
						'class' => !$user || !$user->isVirtual() ? null : 'hide'
					]) ?>
					<?= $this->form->field('virtual_user_id', [
						'type' => 'select',
						'label' => false,
						'list' => $virtualUsers,
						'class' => $user && $user->isVirtual() ? null : 'hide'
					]) ?>
					<?= $this->form->field('user.is_real', [
						'type' => 'checkbox',
						'label' => $t('real user'),
						'checked' => $user ? !$user->isVirtual() : true
					]) ?>
				</div>
			</div>
		</div>

		<div class="grid-row">
			<section class="grid-column-left">
				<?= $this->form->field('name', [
					'type' => 'text',
					'label' => $t('Name')
				]) ?>
				<?= $this->form->field('company', [
					'type' => 'text',
					'label' => $t('Company')
				]) ?>
				<?= $this->form->field('street', [
					'type' => 'text',
					'label' => $t('Street')
				]) ?>
				<?= $this->form->field('city', [
					'type' => 'text',
					'label' => $t('City')
				]) ?>
				<?= $this->form->field('zip', [
					'type' => 'text',
					'label' => $t('ZIP')
				]) ?>
				<?= $this->form->field('country', [
					'type' => 'select',
					'label' => $t('Country'),
					'list' => $countries
				]) ?>
			</section>

			<section class="grid-column-right">
				<?= $this->form->field('phone', [
					'type' => 'phone',
					'label' => $t('Phone')
				]) ?>
			</section>
		</div>
		<div class="bottom-actions">
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>