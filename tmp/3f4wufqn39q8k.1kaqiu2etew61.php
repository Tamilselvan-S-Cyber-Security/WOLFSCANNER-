<?php echo $this->render('templates/parts/headerAdmin.html',NULL,get_defined_vars(),0); ?>
<div id="wrap">
    <?php echo $this->render('templates/parts/leftMenu.html',NULL,get_defined_vars(),0); ?>
    <div class="main">
        <?php echo $this->render('templates/parts/forms/globalSearchForm.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/systemNotification.html',NULL,get_defined_vars(),0); ?>
        <?php echo $this->render('templates/parts/notification.html',NULL,get_defined_vars(),0); ?>

        <div class="columns">
            <div class="column">
                <div class="card">
                    <div class="card-content">
                        <h1 class="title"><?= ($AdminApiTester_page_title) ?></h1>
                        <p class="subtitle is-6 has-text-grey-light"><?= ($AdminApiTester_subtitle) ?></p>
                        <div id="api-tester-labels" class="is-hidden"
                            data-lbl-duration="<?= ($AdminApiTester_meta_duration) ?>"
                            data-lbl-final="<?= ($AdminApiTester_meta_final_url) ?>"
                            data-lbl-redirects="<?= ($AdminApiTester_meta_redirects) ?>"></div>
                        <hr>

                        <div class="api-tester-form box mb-4" style="background: rgba(37, 234, 181, 0.08); border: 1px solid rgba(37, 234, 181, 0.2);">
                            <div class="field is-grouped is-grouped-multiline mb-3">
                                <div class="control">
                                    <label class="label"><?= ($AdminApiTester_method_label) ?></label>
                                    <div class="select">
                                        <select id="api-tester-method">
                                            <option value="GET">GET</option>
                                            <option value="POST">POST</option>
                                            <option value="PUT">PUT</option>
                                            <option value="PATCH">PATCH</option>
                                            <option value="DELETE">DELETE</option>
                                            <option value="HEAD">HEAD</option>
                                            <option value="OPTIONS">OPTIONS</option>
                                            <option value="TRACE">TRACE</option>
                                            <option value="CUSTOM"><?= ($AdminApiTester_method_custom_label) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="control is-expanded" id="api-tester-custom-method-wrap" style="display: none; min-width: 140px;">
                                    <label class="label"><?= ($AdminApiTester_method_custom_label) ?></label>
                                    <input id="api-tester-method-custom" class="input is-family-monospace" type="text" maxlength="32" placeholder="PROPFIND" autocomplete="off">
                                </div>
                                <div class="control is-expanded" style="flex-grow: 2; min-width: 240px;">
                                    <label class="label"><?= ($AdminApiTester_url_label) ?></label>
                                    <input id="api-tester-url" class="input is-family-monospace" type="url" placeholder="<?= ($AdminApiTester_url_placeholder) ?>" maxlength="2048" autocomplete="off">
                                </div>
                                <div class="control is-align-self-flex-end">
                                    <label class="label is-invisible">Actions</label>
                                    <div class="buttons">
                                        <button type="button" id="api-tester-send" class="button is-primary"><?= ($AdminApiTester_send_btn) ?></button>
                                        <button type="button" id="api-tester-clear" class="button is-light"><?= ($AdminApiTester_clear_btn) ?></button>
                                    </div>
                                </div>
                            </div>

                            <div class="field mb-3">
                                <a href="#" id="api-tester-advanced-toggle" class="is-size-7 has-text-link"><?= ($AdminApiTester_advanced_toggle) ?></a>
                            </div>
                            <div id="api-tester-advanced" class="box mb-4" style="display: none; background: rgba(0,0,0,0.15);">
                                <div class="columns is-multiline">
                                    <div class="column is-6-tablet is-3-desktop">
                                        <label class="label is-small"><?= ($AdminApiTester_timeout_label) ?></label>
                                        <input id="api-tester-timeout" class="input" type="number" min="1" max="120" value="30">
                                    </div>
                                    <div class="column is-12-tablet is-9-desktop">
                                        <label class="label is-small is-invisible">Options</label>
                                        <label class="checkbox mr-4"><input type="checkbox" id="api-tester-follow" checked> <?= ($AdminApiTester_follow_redirects) ?></label>
                                        <label class="checkbox mr-4"><input type="checkbox" id="api-tester-verify-ssl" checked> <?= ($AdminApiTester_verify_ssl) ?></label>
                                        <label class="checkbox mr-4"><input type="checkbox" id="api-tester-allow-private"> <?= ($AdminApiTester_allow_private) ?></label>
                                        <label class="checkbox"><input type="checkbox" id="api-tester-include-body"> <?= ($AdminApiTester_include_body) ?></label>
                                    </div>
                                    <div class="column is-12">
                                        <label class="label is-small"><?= ($AdminApiTester_body_mode_label) ?></label>
                                        <div class="select is-fullwidth">
                                            <select id="api-tester-body-mode">
                                                <option value="json"><?= ($AdminApiTester_body_mode_json) ?></option>
                                                <option value="text"><?= ($AdminApiTester_body_mode_text) ?></option>
                                                <option value="xml"><?= ($AdminApiTester_body_mode_xml) ?></option>
                                                <option value="form"><?= ($AdminApiTester_body_mode_form) ?></option>
                                                <option value="none"><?= ($AdminApiTester_body_mode_none) ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field" id="api-tester-body-wrap">
                                <label class="label"><?= ($AdminApiTester_body_label) ?></label>
                                <textarea id="api-tester-body" class="textarea is-family-monospace" rows="7" placeholder='{"key": "value"}'></textarea>
                            </div>
                            <div class="field">
                                <label class="label"><?= ($AdminApiTester_headers_label) ?></label>
                                <textarea id="api-tester-headers" class="textarea is-family-monospace" rows="4" placeholder="Authorization: Bearer …"></textarea>
                            </div>
                        </div>

                        <div id="api-tester-result" class="box" style="display: none;">
                            <div class="level is-mobile mb-3">
                                <div class="level-left">
                                    <div>
                                        <h3 class="title is-5 mb-2"><?= ($AdminApiTester_response_title) ?></h3>
                                        <span class="tag" id="api-tester-status-badge"></span>
                                        <span class="ml-2 has-text-weight-medium" id="api-tester-status-code"></span>
                                        <span class="ml-2 has-text-grey" id="api-tester-status-text"></span>
                                        <span class="ml-2 has-text-grey is-size-7" id="api-tester-duration"></span>
                                    </div>
                                </div>
                                <div class="level-right">
                                    <button type="button" class="button is-small is-light" id="api-tester-copy-headers"><?= ($AdminApiTester_copy_headers) ?></button>
                                    <button type="button" class="button is-small is-light ml-2" id="api-tester-copy-body"><?= ($AdminApiTester_copy_body) ?></button>
                                </div>
                            </div>
                            <p class="is-size-7 has-text-grey mb-2" id="api-tester-meta-line"></p>
                            <div id="api-tester-curl-warning" class="notification is-warning is-light py-2 px-3 mb-3" style="display: none;"></div>
                            <div id="api-tester-truncated" class="notification is-info is-light py-2 px-3 mb-3" style="display: none;"><?= ($AdminApiTester_truncated) ?></div>

                            <div class="tabs is-small mb-2">
                                <ul id="api-tester-res-tabs">
                                    <li class="is-active" data-tab="headers"><a><?= ($AdminApiTester_headers_result_label) ?></a></li>
                                    <li data-tab="body-pretty"><a><?= ($AdminApiTester_body_result_label) ?> — <?= ($AdminApiTester_tab_pretty) ?></a></li>
                                    <li data-tab="body-raw"><a><?= ($AdminApiTester_body_result_label) ?> — <?= ($AdminApiTester_tab_raw) ?></a></li>
                                </ul>
                            </div>
                            <div id="api-tester-pane-headers" class="api-tester-pane">
                                <pre id="api-tester-response-headers" class="has-background-grey-dark has-text-grey-light p-3 is-size-7" style="max-height: 420px; overflow: auto; margin: 0;"></pre>
                            </div>
                            <div id="api-tester-pane-body-pretty" class="api-tester-pane" style="display: none;">
                                <pre id="api-tester-response-body-pretty" class="has-background-grey-dark has-text-grey-light p-3 is-size-7" style="max-height: 420px; overflow: auto; white-space: pre-wrap; word-break: break-word; margin: 0;"></pre>
                            </div>
                            <div id="api-tester-pane-body-raw" class="api-tester-pane" style="display: none;">
                                <pre id="api-tester-response-body-raw" class="has-background-grey-dark has-text-grey-light p-3 is-size-7" style="max-height: 420px; overflow: auto; white-space: pre-wrap; word-break: break-all; margin: 0;"></pre>
                            </div>
                        </div>
                        <div id="api-tester-error" class="notification is-danger is-light" style="display: none;"></div>
                        <div id="api-tester-loading" class="notification is-info is-light" style="display: none;"><?= ($AdminApiTester_loading) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('templates/parts/footerAdmin.html',NULL,get_defined_vars(),0); ?>
