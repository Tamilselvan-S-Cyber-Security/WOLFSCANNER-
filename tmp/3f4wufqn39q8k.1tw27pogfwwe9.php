<?php $SHOW_FILTERS_FORM='/event' === $CURRENT_PATH || '/watchlist' === $CURRENT_PATH; ?>
<?php $SHOW_USERS_TAGS='/watchlist' === $CURRENT_PATH; ?>

<?php if ($SHOW_FILTERS_FORM): ?>
<?php if (isset($showChart) && 1 === $showChart): ?>
    <div class="main-graph separate-graph">
        <?php if ($SHOW_FILTERS_FORM): ?>
            
                <?php echo $this->render('templates/parts/forms/filtersForm.html',NULL,get_defined_vars(),0); ?>
            
        <?php endif; ?>
        <div class="stat-chart"></div>
    </div>
<?php endif; ?>
<?php endif; ?>

<div class="card events-card">
    <header class="card-header">
        <div class="card-header-title">
            <?php if (isset($title)): ?>
                <?= ($title) ?>
                <?php else: ?><?= ($AdminEvents_table_title) ?>
            <?php endif; ?>
            <span>&#8943;</span><p class="tooltip-info tooltip" title="<?= ($AdminEvents_table_title_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
            </p>
        </div>
    </header>

    <div class="card-table">
        <div class="content">

            <?php if ($SHOW_USERS_TAGS): ?>
                
                <div id="important-users">
                    <?php foreach (($IMPORTANT_USERS?:[]) as $RECORD): ?>
                    <div class="control">
                        <div class="tags has-addons">
                            <a class="tag is-link" data-id="<?= ($RECORD['id']) ?>" href="<?= ($BASE) ?>/id/<?= ($RECORD['id']) ?>"><?= ($RECORD['userid']) ?></a>
                            <a class="tag is-delete"></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
            <?php endif; ?>

            <?php if ($SHOW_FILTERS_FORM): ?>
                <?php else: ?>
                <div class="main-graph">
                    <div class="stat-chart"></div>
                </div>
                
            <?php endif; ?>

            <?php if ($SHOW_FILTERS_FORM): ?>
                
                    <?php echo $this->render('templates/parts/forms/searchForm.html',NULL,get_defined_vars(),0); ?>
                    <?php echo $this->render('templates/parts/choices/eventType.html',NULL,get_defined_vars(),0); ?>
                    <?php echo $this->render('templates/parts/choices/rules.html',NULL,get_defined_vars(),0); ?>
                    <?php echo $this->render('templates/parts/choices/deviceType.html',NULL,get_defined_vars(),0); ?>
                
            <?php endif; ?>

            <table class="table dim-table" id="user-events-table">
                <thead>
                    <tr>
                        <?php if (isset($USER)): ?>
                            
                                <th class="tooltip event-user-col" title="<?= ($Base_table_column_session_tooltip) ?>"><?= ($Base_table_column_session) ?></th>
                            
                            <?php else: ?>
                                <th class="tooltip event-user-col" title="<?= ($Base_table_column_user_risk_score_and_email_tooltip) ?>"><?= ($Base_table_column_user_risk_score_and_email) ?></th>
                            
                        <?php endif; ?>
                        <th class="tooltip event-timestamp-col" title="<?= ($Base_table_column_last_action_timestamp_tooltip) ?>"><?= ($Base_table_column_last_action_timestamp) ?></th>
                        <th class="tooltip event-event-type-col" title="<?= ($Base_table_column_event_type_tooltip) ?>"><?= ($Base_table_column_event_type) ?></th>
                        <th class="tooltip event-ip-col" title="<?= ($Base_table_column_ip_tooltip) ?>"><?= ($Base_table_column_ip) ?></th>
                        <th class="tooltip event-ip-type-col" title="<?= ($Base_table_column_ip_type_tooltip) ?>"><?= ($Base_table_column_ip_type) ?></th>
                        <th class="tooltip event-device-col" title="<?= ($Base_table_column_device_tooltip) ?>"><?= ($Base_table_column_device) ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
