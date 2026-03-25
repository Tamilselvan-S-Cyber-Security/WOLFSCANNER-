<?php echo $this->render('templates/parts/headerAdmin.html',NULL,get_defined_vars(),0); ?>
<div id="wrap">
    <?php echo $this->render('templates/parts/panel/eventPanel.html',NULL,get_defined_vars(),0); ?>
    <?php echo $this->render('templates/parts/panel/eventPanel.html',NULL,['fromField'=>true]+get_defined_vars(),0); ?>
    <?php echo $this->render('templates/parts/panel/devicePanel.html',NULL,get_defined_vars(),0); ?>
    <?php echo $this->render('templates/parts/leftMenu.html',NULL,get_defined_vars(),0); ?>
    <div class="main">
        <?php echo $this->render('templates/parts/forms/globalSearchForm.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/systemNotification.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/welcomeMessage.html',NULL,get_defined_vars(),0); ?>

        <?php $titleParam = $RESOURCE['url'] ?: '/'; ?>

        <?php echo $this->render('templates/parts/infoHeader.html',NULL,['title'=>$titleParam,'lastseen'=>$RESOURCE['lastseen'],'subtitle'=>$RESOURCE['title']]+get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/widgets/resource.html',NULL,get_defined_vars(),0); ?>

        <?php echo $this->render('templates/parts/tables/users.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/mapWithIps.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/isps.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/devices.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/fieldAuditTrail.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/events.html',NULL,['showChart'=>1]+get_defined_vars(),0); ?>
    </div>
</div>
<?php echo $this->render('templates/parts/footerAdmin.html',NULL,get_defined_vars(),0); ?>
