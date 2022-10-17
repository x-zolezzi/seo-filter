<?php foreach ($items as $i => $item) : ?>
<?= $this->element($filtre->element, ['filtre' => $filtre, 'item' => $item]) ?>
<?php endforeach; ?>