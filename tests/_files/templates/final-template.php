<?php $this->layout('extended') ?>

<?php $this->section('a-section') ?>

<p>Should BE SUPPLYED by layout.</p>

<?php $this->stop() ?>


<?php $this->section('third') ?>

<p>Should BE APPENDED to extended layout third section.</p>

<?php $this->append() // third ?>


<?php $this->section('another-section') ?>

<p>OVERRIDE a section defined in a PARTIAL</p>

<?php $this->replace() // another-section  ?>

<?= $this->insert('partials\partial-2', ['a_partial_var' => '"!!!"'], ['test_me']) ?>

<p>FINAL TEMPLATE, Out of any section, ouput a var <?= $this->v('test_me') ?></p>
