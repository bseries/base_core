<?php
/**
 * Reusable Copyright Element for Lithium.
 *
 * Works without passing any options at all (except the `holder`), will make
 * "smart" guesses. Allows customization by passing strings for `holder`, `object`
 * or years for `begin` and `end`.
 *
 * Example Usage:
 * ```
 * <?=$this->view()->render(
 *     array('element' => 'copyright'),
 *     array('holder' => 'James Brown')
 * ); ?>
 * ```
 *
 * Example Output:
 * ```
 * © 2011 James Brown. All rights reserved.
 * ```
 *
 * @copyright  2011 David Persson <nperson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       https://gist.github.com/1323139
 */

extract([
	'holder' => null,   // i.e. `'James Brown'`; required.
	'object' => null,   // Additional copyright property to prepend; optional.
	'begin' => null,    // The beginning year i.e. 2009; optional.
	'end' => null,      // The ending year i.e. 2011; optional.
	'minimal' => false  // Leave out the `'All rights reserved.'` appendix.
], EXTR_SKIP);

$end = date('Y');

if (!isset($begin) || $begin == $end) {
	$years = $end;
} elseif ($end - $begin == 1) {
	$years = "{$begin}, {$end}";
} else {
	$years = "{$begin} &ndash; {$end}";
}

?>
<div class="copyright">
	<?php if (isset($object)): ?>
		<?php echo sprintf('%1$s &copy; %2$s %3$s.', ucfirst($object), $years, $holder) ?>
	<?php else: ?>
		<?php echo sprintf('&copy; %1$s %2$s.', $years, $holder) ?>
	<?php endif ?>
	<?php if (!$minimal): ?>
		<?php echo $t('All rights reserved.') ?>
	<?php endif ?>
</div>