<?php echo $this->render('templates/parts/header.html',NULL,get_defined_vars(),0); ?>
<section class="front-block">

    <div class="container">
        <div style="margin: 0 0 10px;">
            <a class="button is-small" href="<?= ($BASE) ?>/" onclick="if (window.history.length > 1) { window.history.back(); return false; }">Back</a>
        </div>
        <code><span style="background:#8080AA;color:#2C2C2C;">&nbsp;A secret place!&nbsp;</span><br>
            <?php if (isset($ERROR_DATA)): ?>
                
                    <p>* <?= ($ERROR_DATA['message']) ?></p>
                    <?php if (isset($ERROR_DATA['extra_message'])): ?>
                        <?php if (isset($ERROR_DATA['raw']) && $ERROR_DATA['raw']): ?>
                            
                                <p>  <?= ($this->raw($ERROR_DATA['extra_message'])) ?></p>
                            
                            <?php else: ?>
                                <p>  <?= ($ERROR_DATA['extra_message']) ?></p>
                            
                        <?php endif; ?>
                    <?php endif; ?>
                
            <?php endif; ?>
        </code>
        <br>
        <div style="text-align:center; padding: 12px 0 0;">
            <a href="<?= ($BASE) ?>/" onclick="if (window.history.length > 1) { window.history.back(); return false; }">
                <img src="<?= ($BASE) ?>/ui/images/error.gif" alt="Error" style="max-width: 100%; max-height: 320px; height: auto; width: 420px; cursor: pointer;" />
            </a>
        </div>
    </div>
</section>

<?php echo $this->render('templates/parts/footer.html',NULL,get_defined_vars(),0); ?>
