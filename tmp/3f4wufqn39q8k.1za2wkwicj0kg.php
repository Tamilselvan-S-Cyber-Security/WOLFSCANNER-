<?php echo $this->render('templates/parts/header.html',NULL,get_defined_vars(),0); ?>
<section class="front-block front-block-login">
    <div class="container">
        <div class="column-login">
            <?php if (isset($ERROR_MESSAGE)): ?>
                
                    <p class="warning-text"><?= ($ERROR_MESSAGE) ?></p>
                
            <?php endif; ?>
            <div class="card">
                <div class="card-panel">
                    <div class="content">
                        <?php echo $this->render('templates/parts/forms/loginForm.html',NULL,get_defined_vars(),0); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $this->render('templates/parts/footer.html',NULL,get_defined_vars(),0); ?>
