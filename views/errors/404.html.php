<?php

use lithium\core\Environment;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($id, $options + ['scope' => 'base_core', 'default' => $message]);
};

$searchEngines = [
	'bing', 'google', 'yahoo', 'altavista',
	'facebook', 'duckduckgo'
];
$referer = $this->_request->env('HTTP_REFERER');

$internalReferal = false;
$searchReferal = false;
$searchTerms = null;

if (strpos($referer, 'owndomainNAME') !== false) {
	$internalReferal = true;
} else {
	foreach ($searchEngines as $searchEngine) {
		if (strpos($referer, "{$searchEngine}.") !== false) {
			$searchReferal = $searchEngine;
			break;
		}
	}
}

if ($internalReferal) {
	// ...
} elseif ($searchReferal) {
	parse_str(urldecode(parse_url($referer, PHP_URL_QUERY)), $query);

	if (isset($query['q'])) {
		$searchTerms = str_replace('+', ' ', $query['q']);
	}
}
?>
<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha">
		<span class="code"><?= $this->_response->status['code'] ?></span>
		<?= $this->title($t('Not Found')) ?>
	</h1>
	<ul class="reason">
		<?php if ($internalReferal): ?>
			<li><?= $t("You've followed a link from within the site. That links seems to be incorrectly setup.") ?></li>
		<?php elseif ($searchReferal): ?>
			<li><?= $t(
				"You did a search on <strong>{:searchReferal}</strong> for <em>{:searchTerms}</em>. However their search index appears to be partially out of date.",
				[
					'searchReferal' => ucfirst($searchReferal),
					'searchTerms' => $searchTerms
				]
			) ?></li>
		<?php elseif ($referer): ?>
			<li><?= $t("If you've followed a link from somewhere the link may be out of date.") ?></li>
		<?php elseif (!$referer): ?>
			<li><?= $t('If you typed in the address, it might have been misspelled.') ?></li>
			<li><?= $t("If you've bookmarked this page the link may be out of date.") ?></li>
		<?php endif ?>
	</ul>
	<ul class="try">
		<? if ($internalReferal): ?>
			<li><?= $t('The administrator of this site has been notified and will fix the link as soon as possible.') ?></li>
			<li>
				<?php echo $t(
					'Go back to the previous page which sent you here <strong>{:url}</strong>.'),
					['url' => $this->html->link($referer)]
				?>
			</li>
		<? elseif ($searchReferal): ?>
		<? elseif ($referer): ?>
		<? elseif (!$referer): ?>
			<li><?= $t('Double-check the spelling of the address.') ?></li>
			<li><?= $t('Update your bookmark.') ?></li>
		<? endif ?>
			<li>
				<?php echo $t(
					'Go to the frontpage at <strong>{:url}</strong>.',
					[
						'url' => $this->html->link(
							$this->url('Pages::home', ['absolute' => true]), 'Pages::home'
						)
					]
				)?>
			</li>
	</ul>
</article>