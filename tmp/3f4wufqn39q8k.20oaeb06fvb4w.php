<?php if (isset($showChart) && 1 === $showChart): ?>
    <div class="main-graph separate-graph">
        <?php if (false !== strpos($CURRENT_PATH, 'isp')): ?>
            
                <?php echo $this->render('templates/parts/forms/filtersForm.html',NULL,get_defined_vars(),0); ?>
            
        <?php endif; ?>
        <div class="stat-chart"></div>
    </div>
<?php endif; ?>

<div class="card events-card">
    <header class="card-header">
        <div class="card-header-title">
            <?= ($AdminIsps_table_title)."
" ?>
            <span>&#8943;</span><p class="tooltip-info tooltip" title="<?= ($AdminIsps_table_title_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?></p>
        </div>
    </header>

    <div class="card-table">
        <div class="content">
            <?php if (false !== strpos($CURRENT_PATH, 'isp')): ?>
                
                    <?php echo $this->render('templates/parts/forms/searchForm.html',NULL,get_defined_vars(),0); ?>
                
            <?php endif; ?>
            <table class="table dim-table" id="isps-table">
                <thead>
                    <tr>
                        <th class="tooltip isp-asn-col" title="<?= ($Base_table_column_asn_tooltip) ?>"><?= ($Base_table_column_asn) ?></th>
                        <th class="tooltip isp-network-col" title="<?= ($Base_table_column_netname_tooltip) ?>"><?= ($Base_table_column_netname) ?></th>
                        <th class="tooltip isp-cnt-col" title="<?= ($Base_table_column_total_actions_tooltip_isps) ?>"><?= ($Base_table_column_total_actions) ?></th>
                        <th class="tooltip isp-cnt-col" title="<?= ($Base_table_column_total_ips_tooltip_isps) ?>"><?= ($Base_table_column_total_ips) ?></th>
                        <th class="tooltip isp-cnt-col" title="<?= ($Base_table_column_total_users_tooltip_isps) ?>"><?= ($Base_table_column_total_users) ?></th>
                        <th class="tooltip isp-cnt-col" title="<?= ($Base_table_column_total_fraud_users_tooltip) ?>"><?= ($Base_table_column_total_fraud_users) ?></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
