<?php echo $this->render('templates/parts/headerAdmin.html',NULL,get_defined_vars(),0); ?>
<div id="wrap">
    <?php echo $this->render('templates/parts/panel/eventPanel.html',NULL,get_defined_vars(),0); ?>
    <?php echo $this->render('templates/parts/panel/eventPanel.html',NULL,['fromField'=>true]+get_defined_vars(),0); ?>
    <?php echo $this->render('templates/parts/panel/devicePanel.html',NULL,get_defined_vars(),0); ?>

    <?php if ($ALLOW_EMAIL_PHONE): ?>
        <?php echo $this->render('templates/parts/panel/emailPanel.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/panel/phonePanel.html',NULL,get_defined_vars(),0); ?>
    <?php endif; ?>

    <?php echo $this->render('templates/parts/leftMenu.html',NULL,get_defined_vars(),0); ?>
    <div class="main">
        <?php echo $this->render('templates/parts/forms/globalSearchForm.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/systemNotification.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/welcomeMessage.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/notification.html',NULL,get_defined_vars(),0); ?>

        <?php echo $this->render('templates/parts/infoHeader.html',NULL,['title'=>isset($USER['email']) ? $USER['email'] : $USER['userid'],'lastseen'=>$USER['lastseen'],'isUserPage'=>true]+get_defined_vars(),0); ?>

        <?php echo $this->render('templates/parts/scoreDetails.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/widgets/userDetails.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/widgets/user.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/mapWithIps.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/isps.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/devices.html',NULL,get_defined_vars(),0); ?>

        <?php if ($ALLOW_EMAIL_PHONE): ?>
            <?php echo $this->render('templates/parts/tables/emails.html',NULL,get_defined_vars(),0); ?>
            <?php echo $this->render('templates/parts/tables/phones.html',NULL,get_defined_vars(),0); ?>
        <?php endif; ?>

        <?php echo $this->render('templates/parts/tables/fieldAuditTrail.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/tables/events.html',NULL,['showChart'=>1]+get_defined_vars(),0); ?>

        <div class="level-right">
            <?php echo $this->render('templates/parts/forms/deleteUserForm.html',NULL,get_defined_vars(),0); ?>
        </div>
    </div>
</div>
<?php echo $this->render('templates/parts/footerAdmin.html',NULL,get_defined_vars(),0); ?>
