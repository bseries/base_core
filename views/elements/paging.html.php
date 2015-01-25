<?php

// Always show first and last and three around current active.
$jumps = range(1, $total);

if (count($jumps) > 10) {
	if ($current > $total -  5) {
		$jumps = array();

		$jumps[] = 1;
		$jumps[] = '...';

		// 3 before, 3 after

		for ($i = ($total - $current) + 3; $i > 0; $i--) {
			$jumps[] = $current - $i;
		}

		// n after, includes current.
		for ($i = $current; $i <= $total; $i++) {
			$jumps[] = $i;
		}

	} elseif ($current > 5) {
		$jumps[] = $total;
		$jumps = array();

		$jumps[] = 1;
		$jumps[] = '...';

		// 3 before, 3 after
		for ($i = 1; $i <= 3; $i++) {
			$jumps[] = $current - $i;
		}
		$jumps[] = $current;

		// 3 before, 3 after
		for ($i = 1; $i <= 3; $i++) {
			$jumps[] = $current + $i;
		}

		$jumps[] = '...';
		$jumps[] = $total;
	} else {
		$jumps = range(1, 8);
		$jumps[] = '...';
		$jumps[] = $total;
	}
}
var_dump($jumps);

?>
<?php if ($total > 0): ?>
	<nav class="nav-paging">
		<?php if ($current - 1 >= 1): ?>
			<?= $this->html->link('prev', ['action' => 'index', 'page' => $current - 1], [
				'rel' => 'prev', 'class' => 'button'
			]) ?>
		<?php endif ?>
		<?php for ($i = 1; $i <= $total; $i++): ?>
			<?= $this->html->link($i, ['action' => 'index', 'page' => $i], [
				'class' => 'button' . ($i === $current ? ' active' : '')
			]) ?>
		<?php endfor ?>
		<?php if ($current + 1 <= $total): ?>
			<?= $this->html->link('next', ['action' => 'index', 'page' => $current + 1], [
				'rel' => 'next', 'class' => 'button'
			]) ?>
		<?php endif ?>
	</nav>
<?php endif ?>