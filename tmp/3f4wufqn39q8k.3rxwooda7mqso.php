<table class="table-tile content info-tiles" id="dashboard-counters">
    <tr>

        <td class="table-card tile-padding totalUsers">
            <div class="subtitle">
                <?= ($AdminHome_total_users) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_users_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/id"><?= ($AdminHome_view_all) ?></a>
        </td>

        <td class="table-card tile-padding totalIps">
            <div class="subtitle">
                <?= ($AdminHome_total_ips) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_ips_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/ip"><?= ($AdminHome_view_all) ?></a>
        </td>

        <td class="table-card tile-padding totalCountries">
            <div class="subtitle">
                <?= ($AdminHome_total_countries) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_countries_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/country"><?= ($AdminHome_view_all) ?></a>
        </td>

        <td class="table-card tile-padding totalUrls">
            <div class="subtitle">
                <?= ($AdminHome_total_urls) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_urls_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/resource"><?= ($AdminHome_view_all) ?></a>
        </td>

        <td class="table-card tile-padding totalUsersForReview">
            <div class="subtitle">
                <?= ($AdminHome_total_users_for_review) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_users_for_review_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/review-queue"><?= ($AdminHome_view_all) ?></a>
        </td>

        <td class="table-card tile-padding totalBlockedUsers">
            <div class="subtitle">
                <?= ($AdminHome_total_blocked_users) ?><p class="tooltip-info tooltip" title="<?= ($AdminHome_total_blocked_users_tooltip) ?>"><?php echo $this->render('images/icons/information.svg',NULL,get_defined_vars(),0); ?>
                </p>
            </div>
            <p class="title"></p>
            <a href="<?= ($BASE) ?>/blacklist"><?= ($AdminHome_view_all) ?></a>
        </td>
    </tr>
</table>
