<?php

use lithium\core\Environment;
use li3_flash_message\extensions\storage\FlashMessage;
use lithium\security\Auth;
use \DateTime;
use \IntlDateFormatter;
use cms_core\extensions\cms\Modules;
use cms_core\extensions\cms\Settings;

$site = Settings::read('site');
$locale = Environment::get('locale');

$flash = FlashMessage::read();
FlashMessage::clear();

?>
<!doctype html>
<html lang="<?= strtolower(str_replace('_', '-', $locale)) ?>">
	<head>
		<?php echo $this->html->charset() ?>
		<title><?php echo ($title = $this->title()) ? "{$title} - " : null ?>Admin – <?= $site['title'] ?></title>
		<link rel="icon" href="<?= $this->assets->url('/core/ico/admin.png') ?>">

		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<!--[if lt IE 9]>>
			<script src="<?= $this->assets->url('/core/js/compat/html5shiv.js') ?>"></script>
		<![endif]-->

		<?php echo $this->assets->style([
			'/core/css/reset',
			'/core/css/admin'
		]) ?>
		<?php echo $this->assets->script([
			'/core/js/underscore',
			'/core/js/jquery',
			'/core/js/require',
			'/core/js/base',
			'/media/js/base'
		]) ?>
		<?php echo $this->styles() ?>
		<?php echo $this->scripts() ?>
		<?php if (!empty(Settings::read('googleAnalytics.default'))): ?>
			<?=$this->view()->render(['element' => 'ga'], [], [
				'library' => 'cms_core'
			]) ?>
		<?php endif ?>
		<script>
			<?php $url = ['controller' => 'files', 'library' => 'cms_media', 'admin' => true] ?>

			App.env = $.extend(App.env, {
				media: {
					endpoints: {
						index: '<?= $this->url($url + ['action' => 'api_index']) ?>',
						view: '<?= $this->url($url + ['action' => 'api_view', 'id' => '__ID__']) ?>',
						transfer: '<?= $this->url($url + ['action' => 'api_transfer']) ?>'
					}
				}
			});

			require(['form']);
		</script>
	</head>
	<body class="layout-admin">
		<div
			id="messages"
			<?php if ($flash): ?>
				data-flash-message="<?= $flash['message'] ?>"
				data-flash-level="<?= isset($flash['attr']['level']) ? $flash['attr']['level'] : 'neutral' ?>"
			<?php endif ?>
		>
		</div>

		<div id="modal" class="hide">
			<div class="controls"><div class="close">╳</div></div>
			<div class="content"></div>
		</div>
		<div id="modal-overlay" class="hide"></div>

		<div id="container">
			<header class="main">
				<h1>
					<?= $this->html->link($site['title'], ['controller' => 'pages', 'action' => 'home', 'library' => 'cms_core']) ?>
				</h1>
				<div id="user">
					<?php if ($authedUser = Auth::check('default')): ?>
						<div class="inner">
							<div class="left">
								<img class="avatar" src="https://www.gravatar.com/avatar/<?= md5($authedUser['email'] )?>.jpg?s=200&d=retro"></span>
							</div>
							<div class="right">
								<div class="welcome">
									<?php echo $t('Moin {:name}!', [
										'name' => '<span class="name">' . strtok($authedUser['name'], ' ') . '</span>'
									]) ?>
								</div>
								<?php
									$today = new DateTime();
									$formatter = new IntlDateFormatter(
										'de_DE',
										IntlDateFormatter::FULL,
										IntlDateFormatter::NONE
									);
								?>
								<time class="today" datetime="<?= $today->format(DateTime::W3C) ?>">
									<?= $formatter->format($today) ?>
								</time>
							</div>
						</div>
						<div class="actions">
							<?= $this->html->link($t('Site'), '/', ['target' => 'new']) ?>
							<?= $this->html->link($t('Dashboard'), ['controller' => 'pages', 'action' => 'home', 'library' => 'cms_core']) ?>
							<?= $this->html->link($t('Logout'), ['controller' => 'users', 'action' => 'logout', 'library' => 'cms_core', 'admin' => true]) ?>
						</div>
					<?php endif ?>
				</div>
			</header>
			<nav id="main">
				<?php foreach (Modules::read() as $module): ?>
						<?= $this->html->link($module['title'], $module['url']() ?: [
							'controller' => $module['name'], 'action' => 'index', 'library' => $module['library']
						]) ?>
				<?php endforeach ?>
				<?= $this->html->link($t('Support'), ['controller' => 'pages', 'action' => 'support', 'library' => 'cms_core']) ?>
			</nav>
			<div id="content">
				<?php echo $this->content(); ?>
			</div>
		</div>
		<footer>
			<?=$this->view()->render(['element' => 'copyright'], [
				'holder' => $this->html->link(
					'Atelier Disko',
					'http://atelierdisko.de',
					['target' => 'new']
				)
			], ['library' => 'cms_core']) ?>

			<div class="credits">
				<?php echo $t('Powered by {:name}.', [
					'name' => $this->html->link('Bureau', 'http://atlierdisko.de', ['target' => 'new'])
				]) ?>
			</div>
		</footer>
	</body>
</html>