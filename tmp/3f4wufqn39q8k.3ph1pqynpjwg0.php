<?php echo $this->render('templates/parts/header.html',NULL,get_defined_vars(),0); ?>
<section class="front-block">
    <div class="container">
        <div class="column-login">
            <div class="card">
                <div class="card-panel">
                    <div class="content">
                        <?php echo $this->render('templates/parts/forms/logoutForm.html',NULL,get_defined_vars(),0); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $this->render('templates/parts/footer.html',NULL,get_defined_vars(),0); ?>
