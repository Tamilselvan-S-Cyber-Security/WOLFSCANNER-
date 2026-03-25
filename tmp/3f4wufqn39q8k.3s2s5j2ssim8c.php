<?php echo $this->render('templates/parts/headerAdmin.html',NULL,get_defined_vars(),0); ?>
<div id="wrap">
    <?php echo $this->render('templates/parts/leftMenu.html',NULL,get_defined_vars(),0); ?>
    <div class="main">
        <?php echo $this->render('templates/parts/forms/globalSearchForm.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/systemNotification.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/isps.html',NULL,['showChart'=>1]+get_defined_vars(),0); ?>
    </div>
</div>
<?php echo $this->render('templates/parts/footerAdmin.html',NULL,get_defined_vars(),0); ?>
