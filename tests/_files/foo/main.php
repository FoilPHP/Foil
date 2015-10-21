<?php $this->section('one') ?>
    Hello <?= $this->foo.' ' ?>
<?php $this->stop() ?>

    Alone

<?php $this->section('two') ?>
    NO
<?php $this->stop() ?>

<?php $this->section('three') ?>
    YES
<?php
$this->stop();
