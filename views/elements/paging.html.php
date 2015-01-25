<?php

var_dump($paginator->getPages());die;

foreach ($paginator as $i) {
var_dump($i);
}
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