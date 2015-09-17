<?php

use lithium\util\Inflector;
use lithium\core\Libraries;
use lithium\core\Environment;
use li3_flash_message\extensions\storage\FlashMessage;
use base_core\extensions\cms\Settings;
use base_core\models\Assets;

$site = Settings::read('site');
$locale = Environment::get('locale');

$flash = FlashMessage::read();
FlashMessage::clear();

?>
<!doctype html>
<html lang="<?= strtolower(str_replace('_', '-', $locale)) ?>">
	<head>
		<?php echo $this->html->charset() ?>
		<title>
			<?php if (in_array($this->_request->action, ['home', 'front'])): ?>
				<?php echo $this->title() ?>
			<?php else: ?>
				<?php echo ($title = $this->title()) ? "{$title} – " : null ?><?= $site['title'] ?>
			<?php endif ?>
		</title>
		<link rel="icon" href="<?= $this->assets->url('/app/ico/app.png') ?>">
		<?php if (isset($seo['description'])): ?>
			<meta name="description" content="<?= $seo['description'] ?>">
		<?php endif ?>

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<?php echo $this->assets->style([
			'/app/css/reset',
			'/app/css/base'
		]) ?>
		<?php echo $this->styles() ?>
		<?=$this->view()->render(
			['element' => 'define_app'],
			['admin' => false, 'routes' => $routes],
			['library' => 'base_core']
		) ?>
		<?php
			$scripts = array_merge(
				['/app/js/require'],
				$this->assets->availableScripts('base'),
				$this->assets->availableScripts('view'),
				$this->assets->availableScripts('layout')
			);
		?>
		<?php echo $this->assets->script($scripts) ?>
		<?php echo $this->scripts() ?>
		<?php if (Settings::read('service.googleAnalytics.default.account') && !PROJECT_DEBUG): ?>
			<?=$this->view()->render(['element' => 'ga'], [], [
				'library' => 'base_core'
			]) ?>
		<?php endif ?>
		<?=$this->view()->render(['element' => 'head'], [], [
			'library' => 'app'
		]) ?>
	</head>
	<?php
		$classes = ['layout-default'];

		if ($authedUser) {
			$classes[] = 'user-authed';

		}
		if (isset($device)) {
			foreach ($device as $name => $flag) {
				if (is_bool($flag) && $flag ) {
					$classes[] = 'device-' . strtolower(Inflector::slug($name));
				} elseif (is_string($flag)) {
					$classes[] = 'device-' . strtolower(Inflector::slug($name)) . '-' . strtolower($flag);
				}
			}
		}
		if (isset($extraBodyClasses)) {
			$classes = array_merge($classes, $extraBodyClasses);
		}
	?>
	<body class="<?= implode(' ', $classes) ?>">
		<?=$this->view()->render(['element' => 'messages'], compact('flash'), [
			'library' => 'base_core'
		]) ?>

		<div id="container">
			<?=$this->view()->render(['element' => 'header'], compact('authedUser', 'nav'), [
				'library' => 'app'
			]) ?>

			<div id="content">
				<?php echo $this->content() ?>
			</div>
		</div>
		<?=$this->view()->render(['element' => 'footer'], compact('authedUser', 'nav'), [
			'library' => 'app'
		]) ?>
	</body>
</html>