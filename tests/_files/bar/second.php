<?php $this->layout('main', ['foo' => 'Bar!']) ?>

<?php $this->section('one') ?>
World <?= $this->foo ?>
<?php $this->stop() ?>

<?php $this->section('two') ?>
I Win
<?php $this->replace() ?>

Buffalo Bill

<?php $this->section('three') ?>
MAN
<?php
$this->stop();
