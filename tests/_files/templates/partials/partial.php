<?php $this->section('another-section') ?>

<p>A SECTION defined in a PARTIAL</p>

<?php $this->stop() ?>

<p>
    I am a partial. And this: &quot;<?= $this->test_me ?>&quot; is a var I share with template, but
    this <?= $this->v('a_partial_var') ?> is a specific partial var.
</p>
