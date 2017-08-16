<?php

// use Exception;
use base_core\extensions\cms\Settings;
use lithium\net\http\Router;

extract([
	'type' => null, // either `'single'` or `'multiple'`
	'item' => null // optional, but usually provided with single type
], EXTR_SKIP);

$clientRequest = clone $this->request();
$clientRequest->persist = [];

if ($backlink = Settings::read('backlink')) {
	$urls = $backlink($type, $item);

	if ($urls === false) {
		$message  = "Backlinker failed for type `{$type}` and item: " . var_export($item->data());
		throw new Exception($message);
	}
	if ($urls !== []) {
		foreach ($urls as $url) {
			$url = Router::match($url, $clientRequest, ['scope' => 'app', 'absolute' => true]);

			echo $this->html->link(str_replace(['http://', 'https://'], null, $url), $url, [
				'class' => 'button plain inverse backlink',
				'target' => 'new'
			]);
		}
	}
}

?>