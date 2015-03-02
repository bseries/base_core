<?php

// In case not all parts of the address should be visible,
// use CSS to hide these elements selectively.

// General options for this element.
extract([
	'displayName' => null,
], EXTR_SKIP);

// Data structure.
$item += [
	'name' => null,
	'address_line_1' => null,
	'postal_code' => null,
	'locality' => null,
	'dependent_locality' => null,
	'country' => null,
	'phone' => null,
	'email' => null,
	'website' => null
];

?>
<?php if (!empty($item['organization'])): ?>
	<article class="contact" itemscope itemtype="http://data-vocabulary.org/Organization">
<?php else: ?>
	<article class="contact" itemscope itemtype="http://data-vocabulary.org/Person">
<?php endif ?>
	<p>
		<?php if ($displayName): ?>
			<span class="name">
				<?= $displayName ?>
				<meta itemprop="name" value="<?= $item['name'] ?>" />
			</span>
		<?php else: ?>
			<span class="name" itemprop="name"><?= $item['name'] ?></span>
		<?php endif ?>
		<br/>
		<div itemprop="address" itemtype="http://data-vocabulary.org/Address" itemscope>
			<span class="address-line" itemprop="street-address"><?= $item['address_line_1'] ?></span><br>
			<span class="postal-code" itemprop="postal-code"><?= $item['postal_code']?></span>
			<span class="locality" itemprop="locality"><?= $item['locality'] ?></span><br>
			<?php if ($item['dependent_localityt']): ?>
				<span class="dependent-locality"><?= $item['dependent_locality'] ?></span>
			<?php endif ?>
			<?php if ($item['country']): ?>
				<span itemprop="country" class="country"><?= $item['country'] ?></span>
			<?php endif ?>
		</div>
	</p>
	<p>
		<?php if ($item['phone']): ?>
			<label><?= $t('Phone') ?>:</label>
			<span class="phone" itemprop="tel"><?= $item['phone'] ?></span>
			<br>
		<?php endif ?>

		<label><?= $t('E–Mail') ?>:</label>
		<?= $this->html->link($item['email'], "mailto:{$item['email']}", ['class' => 'email']) ?>
		<br>

		<?php if ($item['website']): ?>
			<label><?= $t('Website')?>:</label> <a class="website" itemprop="url" href="<?= $item['website'] ?>" target="new">
				<?= parse_url($item['website'], PHP_URL_HOST)?>
			</a>
		<?php endif ?>
	</p>
</article>