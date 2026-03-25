<?php echo $this->render('templates/parts/headerAdmin.html',NULL,get_defined_vars(),0); ?>
<div id="wrap">
    <?php echo $this->render('templates/parts/leftMenu.html',NULL,get_defined_vars(),0); ?>
    <div class="main">
        <?php echo $this->render('templates/parts/popups/closeAccountPopup.html',NULL,get_defined_vars(),0); ?>

        <?php echo $this->render('templates/parts/forms/globalSearchForm.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/systemNotification.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/welcomeMessage.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/notification.html',NULL,get_defined_vars(),0); ?>

        <div class="columns">
            <div class="column settings-page">
                <?php echo $this->render('templates/parts/forms/checkUpdatesForm.html',NULL,get_defined_vars(),0); ?>
                <?php echo $this->render('templates/parts/forms/timezoneForm.html',NULL,get_defined_vars(),0); ?>

                <?php if ($IS_OWNER): ?>
                    <?php foreach (($API_KEYS?:[]) as $API_KEY): ?>
                        <?php echo $this->render('templates/parts/forms/retentionPolicyForm.html',NULL,['apiKeyId'=>$API_KEY['id'] ,'retentionPolicy'=>$API_KEY['retention_policy']]+get_defined_vars(),0); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php $FREQUENCY=isset($VALUES)?$VALUES['unreviewed_items_reminder_freq']:$PROFILE->reminderFreq; ?>
                <?php echo $this->render('templates/parts/forms/notificationPreferencesForm.html',NULL,['frequency'=>$FREQUENCY]+get_defined_vars(),0); ?>

                <?php if ($IS_OWNER): ?>
                    <?php echo $this->render('templates/parts/tables/sharedOperators.html',NULL,get_defined_vars(),0); ?>
                <?php endif; ?>

                <?php echo $this->render('templates/parts/forms/changePasswordForm.html',NULL,get_defined_vars(),0); ?>

                <?php $EMAIL=$PROFILE ? $PROFILE->email : ''; ?>
                <?php $EMAIL=isset($EMAIL_VALUES) ? $EMAIL_VALUES['email'] : $EMAIL; ?>
                <?php echo $this->render('templates/parts/forms/changeEmailForm.html',NULL,['email'=>$EMAIL]+get_defined_vars(),0); ?>

                <?php echo $this->render('templates/parts/forms/closeAccountForm.html',NULL,get_defined_vars(),0); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('templates/parts/footerAdmin.html',NULL,get_defined_vars(),0); ?>
