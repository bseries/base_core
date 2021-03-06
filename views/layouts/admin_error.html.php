<?php

use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'base_core', 'default' => $message]);
};

?>
<!doctype html>
<html lang="<?= strtolower(str_replace('_', '-', $locale)) ?>">
	<head>
		<!-- Basics -->
		<?php echo $this->html->charset() ?>
		<link rel="icon" href="<?= $this->assets->url('/base-core/ico/admin.png') ?>">

		<!-- SEO -->
		<?php echo $this->seo->title() ?>

		<!-- Compatibility -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<!-- Styles -->
		<?php
			$styles = array_merge(
				$this->assets->availableStyles('base', ['admin' => true]),
				$this->assets->availableStyles('view', ['admin' => true]),
				$this->assets->availableStyles('layout', ['admin' => true])
			);
		?>
		<?php echo $this->assets->style($styles) ?>
		<?php echo $this->styles() ?>
	</head>
	<?php
		$classes = ['layout-admin-error'];

		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<div id="container">
			<div id="content">
				<?php echo $this->content() ?>
			</div>
		</div>
	</body>
</html>