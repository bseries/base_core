<?php

use base_core\extensions\cms\Settings;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

?>
<article class="error-page error-page--generic">
	<h1 class="h-super-alpha">
		<span class="error-page__code"><?= $this->_response->status['code'] ?></span>
		<span class="error-page__message"><?= $this->title($this->_response->status['message']) ?></span>
	</h1>
	<div class="error-page__reason t-beta">
		<?= $t("Ooops, this shouldn’t have happened.") ?>
	</div>
	<div class="error-page__id">
		<?php echo $t('The ID for this error is <strong>{:id}</strong>', [
			'id' => $errorId
		]) ?>
	</div>
	<div class="error-page__try">
		<?php if (Settings::read('contactSupport.enabled')): ?>
			<?= $this->html->link(
				$t('Contact support'),
				Settings::read('contactSupport.url'),
				['target' => 'new', 'class' => 'button']
			) ?>
		<?php endif ?>
			<?= $this->html->link(
				$t('Go back to dashboard'),
				'/admin',
				['class' => 'button']
			) ?>
	</div>
</article>