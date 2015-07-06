<?php $this->layout('layout') ?>

<p>EXTENDED LAYOUT, Out of any section, output a deep var <?= $this->v('a.var.in.extended') ?></p>

<?php $this->section('first') ?>

<p>Should BE APPENDED to layout first section.</p>

<?php $this->append() // first ?>

<?php $this->section('first-child') ?>

<p>Should REPLACE layout first-child layout section.</p>

<?php $this->replace() // first-child ?>

<?php $this->section('second') ?>

<p>Should REPLACE layout second section.</p>

<div>

    <?php $this->section('third') ?>
    <p>Extended layout third section.</p>
    <?php $this->append() // third ?>

</div>

<?php $this->replace(); // second ?>

<div>
    <?= $this->buffer() ?>
</div>
