<?php $this->block('spaceless') ?>
    <?php $this->block('wrap', '<div>', '</div>') ?>
        <?php $this->block('spaceless') ?>
            <ul>
                <?php $this->block('repeat', 3) ?>
                <li>a</li>
                <?php $this->endblock('repeat'); ?>
            </ul>
        <?php $this->endblock('spaceless') ?>
    <?php $this->endblock('wrap') ?>
<?php $this->endblock('spaceless') ?>
