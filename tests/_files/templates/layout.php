<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $this->v('title', 'Default Title') ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?= $this->ww('menu', '<div><ul>%s</ul></div>', '<li><a href="%s">%s</a></li>') ?>

<?= $this->wwif('menu', false, '<div>%s</div>', '<li><a href="%s">%s</a></li>') ?>

<div>

    <p>Deep var test: <?= $this->v('a.pretty.deep.var') ?></p>

    <p>Default test: <?= $this->v('i_do_not_exist', 'I am a default.') ?></p>

    <p>__get test: <?= $this->test_me ?></p>

    <p>Raw test: <?= $this->raw('html_content') ?></p>

    <p>Autoescape test: <?= $this->html_content ?></p>

    <p>Filter test: <?= $this->f('uppercase|reverse', 'lowercase') ?></p>

    <p>Advanced test: <?= $this->v('a.var.0|uppercase|reverse') ?></p>

</div>

<div>
    <?= $this->returnSomething() ?>
</div>

<div>
    <?= $this->insert('partials/partial', ['a_partial_var' => '"I am a partial var!"']) ?>
</div>

<div>
    <?= $this->raw('i_do_not_exist',
        $this->insert('partials/partial-2', ['a_partial_var' => '"I am a partial var too."'])) ?>
</div>

<div>
    <?= $this->supply('a-section') ?>
</div>

<div>
    <?= $this->supply('a-non-existent-section', 'I am here.') ?>
</div>

<div>

    <?php $this->section('first') ?>

    <p>MAIN LAYOUT, first section</p>

    <div>

        <?php $this->section('first-child') ?>

        <p>MAIN LAYOUT, first child section</p>

        <?php $this->stop() // first-child   ?>

    </div>

    <p>MAIN LAYOUT, first section after child</p>

    <?php $this->stop() // first    ?>

</div>

<div>

    <?php $this->section('second') ?>

    <p>MAIN LAYOUT, second section</p>

    <?php $this->stop() // second    ?>

</div>

<div>
    <?= $this->buffer() ?>
</div>

</body>
</html>
