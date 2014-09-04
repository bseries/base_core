<?php

$this->set([
	'page' => [
		'type' => 'standalone',
		'object' => $t('Login')
	],
	'extraBodyClasses' => ['layout-admin-session']
]);

?>
<article class="view-<?= $this->_config['controller'] . '-' . str_replace('_', '-', $this->_config['template']) ?>">
	<?=$this->form->create(null, ['url' => ['action' => 'login', 'library' => 'base_core']]) ?>
		<?=$this->form->field('email', ['type' => 'email', 'label' => 'E–Mail']) ?>
		<?=$this->form->field('password', ['type' => 'password', 'label' => 'Passwort']) ?>
		<?=$this->form->button($t('Login'), ['type' => 'submit', 'class' => 'large button login']) ?>
	<?=$this->form->end() ?>
</article>