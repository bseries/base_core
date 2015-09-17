<div
	id="messages"
	<?php if ($flash): ?>
		data-flash-message="<?= $flash['message'] ?>"
		data-flash-level="<?= isset($flash['attrs']['level']) ? $flash['attrs']['level'] : 'neutral' ?>"
	<?php endif ?>
></div>
