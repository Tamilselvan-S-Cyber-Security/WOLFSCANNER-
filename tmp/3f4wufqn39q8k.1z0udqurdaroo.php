<table class="table" id="token-table">
    <thead>
        <tr>
            <th><?= ($AdminApi_table_column_sensor_url) ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code><?= ($API_URL) ?></code></td>
        </tr>
    </tbody>
</table>
<table class="table" id="token-table">
    <thead>
        <tr>
            <th><?= ($AdminApi_table_column_sensor_key) ?></th>
            <th><?= ($AdminApi_table_column_created_at) ?></th>
            <th class="tooltip action-button-col" title="<?= ($AdminApi_table_column_action_tooltip) ?>"><?= ($AdminApi_table_column_action) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (($API_KEYS?:[]) as $API_KEY): ?>
            <tr>
                <td><code><?= ($API_KEY['key']) ?></code></td>
                <td><?= (\Wolf\Utils\ElapsedDate::short($API_KEY['created_at'])) ?></td>
                <td>
                    <?php echo $this->render('templates/parts/forms/resetKeyForm.html',NULL,['id'=>$API_KEY['id'],'isOwner'=>$IS_OWNER]+get_defined_vars(),0); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
