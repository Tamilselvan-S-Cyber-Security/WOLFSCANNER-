<div class="sidebar">

    <aside class="menu">

        <?php echo $this->render('templates/parts/logoAdmin.html',NULL,get_defined_vars(),0); ?>

        <ul class="menu-list">

            <li>

                <?php $REVIEW_QUEUE_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/review-queue') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($REVIEW_QUEUE_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/review-queue">

                    <?php if ($NUMBER_OF_NOT_REVIEWED_USERS > 0): ?>

                        <span class="reviewed-users-tile"><?= ($NUMBER_OF_NOT_REVIEWED_USERS) ?></span>

                    <?php endif; ?><?= ($LeftMenu_not_reviewed_users_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $BLACKLIST_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/blacklist') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($BLACKLIST_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/blacklist">

                    <?php if ($NUMBER_OF_BLACKLIST_USERS > 0): ?>

                        <span class="blacklist-users-tile"><?= ($NUMBER_OF_BLACKLIST_USERS) ?></span>

                    <?php endif; ?><?= ($LeftMenu_blacklist_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $HOME_ACTIVE_CLASS='/' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($HOME_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/">

                    <?php echo $this->render('images/icons/dashboard.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_home_link)."
" ?>

                </a>

            </li>

            

            <li>

                <?php $EVENTS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/event') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($EVENTS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/event">

                    <?php echo $this->render('images/icons/events.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_all_events_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $USERS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/id') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($USERS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/id">

                    <?php echo $this->render('images/icons/users.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_users_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $IPS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/ip') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($IPS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/ip">

                    <?php echo $this->render('images/icons/ips.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_ips_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $COUNTRIES_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/country') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($COUNTRIES_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/country">

                    <?php echo $this->render('images/icons/countries.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_countries_link)."
" ?>

                </a>

            </li>



            

            <li>

                <?php $ISPS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/isp') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($ISPS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/isp">

                    <?php echo $this->render('images/icons/isp.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_isps_link)."
" ?>

                </a>

            </li>



            <?php if ($ALLOW_EMAIL_PHONE): ?>

                <li>

                    <?php $DOMAINS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/domain') ? 'is-active': 'is-normal'; ?>

                    <a class="<?= ($DOMAINS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/domain">

                        <?php echo $this->render('images/icons/domains.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_domains_link)."
" ?>

                    </a>

                </li>

            <?php endif; ?>



            <li>

                <?php $RESOURCES_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/resource') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($RESOURCES_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/resource">

                    <?php echo $this->render('images/icons/resources.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_resources_link)."
" ?>

                </a>

            </li>

            

            

            <li>

                <?php $FIELD_AUDIT_TRAIL_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/field') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($FIELD_AUDIT_TRAIL_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/field">

                    <?php echo $this->render('images/icons/history.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_field_audit_trail_link)."
" ?>

                </a>

            </li>

            

            <li>

                <?php $RULES_ACTIVE_CLASS='/rules' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($RULES_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/rules">

                    <?php echo $this->render('images/icons/alert-dashboard.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_rules_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $VULN_ACTIVE_CLASS='/vuln' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($VULN_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/vuln">

                    <?php echo $this->render('images/icons/auto-scanner.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_auto_scanner_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $INTEGRITY_SCANNER_ACTIVE_CLASS='/integrity-auto-scanner' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($INTEGRITY_SCANNER_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/integrity-auto-scanner">

                    <?php echo $this->render('images/icons/integrity-auto-scanner.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_integrity_auto_scanner_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $VIRUS_SCAN_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/virus-scan') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($VIRUS_SCAN_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/virus-scan">

                    <?php echo $this->render('images/icons/virus-scan.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_virus_scan_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $RISK_CORRELATION_ACTIVE_CLASS='/risk-correlation' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($RISK_CORRELATION_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/risk-correlation">

                    <?php echo $this->render('images/icons/risk-correlation.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_risk_correlation_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $DEBUGGER_ACTIVE_CLASS='/debugger' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($DEBUGGER_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/debugger">

                    <?php echo $this->render('images/icons/debugger.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_debugger_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $JWT_VALIDATOR_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/jwt-validator') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($JWT_VALIDATOR_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/jwt-validator">

                    <?php echo $this->render('images/icons/jwt-validator.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_jwt_validator_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $API_TESTER_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/api-tester') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($API_TESTER_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/api-tester">

                    <?php echo $this->render('images/icons/apikeys.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_api_tester_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $API_KEYS_ACTIVE_CLASS='/api' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($API_KEYS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/api">

                    <?php echo $this->render('images/icons/apikeys.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_api_keys_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $SETTINGS_ACTIVE_CLASS='/settings' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($SETTINGS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/settings">

                    <?php echo $this->render('images/icons/settings.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_settings_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $LOGBOOK_ACTIVE_CLASS='/logbook' == $CURRENT_PATH ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($LOGBOOK_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/logbook">

                    <?php echo $this->render('images/icons/rules.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_logbook_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $REPORT_SENDER_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/report-sender') ? 'is-active': 'is-normal'; ?>

                <a class="<?= ($REPORT_SENDER_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?>/report-sender">

                    <?php echo $this->render('images/icons/email.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_report_sender_link)."
" ?>

                </a>

            </li>



            <li>

                <a class="is-normal" href="<?= ($BASE) ?>/logout">

                    <?php echo $this->render('images/icons/logout.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_logout_link)."
" ?>

                </a>

            </li>



            <li>

                <?php if (0 === strpos($LeftMenu_community_link_url, 'http')): ?>

                    

                        <a class="is-normal" href="<?= ($LeftMenu_community_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    

                    <?php else: ?>

                        <a class="is-normal" href="<?= ($BASE) ?><?= ($LeftMenu_community_link_url) ?>">

                    

                <?php endif; ?>

                    <?php echo $this->render('images/icons/community.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_community_link)."
" ?>

                </a>

            </li>



            <li>

                <a class="is-normal" href="<?= ($LeftMenu_face_time_support_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    <?php echo $this->render('images/icons/voip.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_face_time_support_link)."
" ?>

                </a>

            </li>



            <li>

                <?php if (0 === strpos($LeftMenu_issues_link_url, 'http')): ?>

                    

                        <a class="is-normal" href="<?= ($LeftMenu_issues_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    

                    <?php else: ?>

                        <a class="is-normal" href="<?= ($BASE) ?><?= ($LeftMenu_issues_link_url) ?>">

                    

                <?php endif; ?>

                    <?php echo $this->render('images/icons/resources.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_issues_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $DEV_DOCS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/docs/developer') ? 'is-active': 'is-normal'; ?>

                <?php if (0 === strpos($LeftMenu_dev_docs_link_url, 'http')): ?>

                    

                        <a class="<?= ($DEV_DOCS_ACTIVE_CLASS) ?>" href="<?= ($LeftMenu_dev_docs_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    

                    <?php else: ?>

                        <a class="<?= ($DEV_DOCS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?><?= ($LeftMenu_dev_docs_link_url) ?>">

                    

                <?php endif; ?>

                    <?php echo $this->render('images/icons/developer.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_dev_docs_link)."
" ?>

                </a>

            </li>



            <li>

                <?php $ADMIN_DOCS_ACTIVE_CLASS=false !== strpos($CURRENT_PATH, '/docs/admin') ? 'is-active': 'is-normal'; ?>

                <?php if (0 === strpos($LeftMenu_admin_docs_link_url, 'http')): ?>

                    

                        <a class="<?= ($ADMIN_DOCS_ACTIVE_CLASS) ?>" href="<?= ($LeftMenu_admin_docs_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    

                    <?php else: ?>

                        <a class="<?= ($ADMIN_DOCS_ACTIVE_CLASS) ?>" href="<?= ($BASE) ?><?= ($LeftMenu_admin_docs_link_url) ?>">

                    

                <?php endif; ?>

                    <?php echo $this->render('images/icons/settings.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_admin_docs_link)."
" ?>

                </a>

            </li>



            <li>

                <?php if (0 === strpos($LeftMenu_bbp_hub_link_url, 'http')): ?>

                    

                        <a class="is-normal" href="<?= ($LeftMenu_bbp_hub_link_url) ?>" target="_blank" rel="noopener noreferrer">

                    

                    <?php else: ?>

                        <a class="is-normal" href="<?= ($BASE) ?><?= ($LeftMenu_bbp_hub_link_url) ?>">

                    

                <?php endif; ?>

                    <?php echo $this->render('images/icons/bbp-hub.svg',NULL,get_defined_vars(),0); ?><?= ($LeftMenu_bbp_hub_link)."
" ?>

                </a>

            </li>



        </ul>

    </aside>

</div>