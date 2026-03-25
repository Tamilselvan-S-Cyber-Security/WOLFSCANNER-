<div id="rule-selectors" class="settings-page choices-selector">
    <div class="field">
        <div class="control">
            <div class="selector">
                <select class="input" name="version" multiple>
                    <option placeholder disabled><?= ($AdminUsers_rules_search_placeholder) ?></option>
                    <?php foreach (($RULES?:[]) as $record): ?>
                        <option value="<?= ($record['uid']) ?>" <?= (isset($DEFAULT_RULE) && $DEFAULT_RULE === $record['uid'] ? 'selected' : '')."
" ?>
                            ><?= ($record['uid']) ?>|<?= (\Wolf\Utils\Assets\RulesClasses::getRuleClass($record['value'], $record['broken'])) ?>|<?= ($record['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>
