<div class="selector">
    <select class="input" name="rules-preset" id="rules-preset">
        <option value="none" disabled selected hidden><?= ($AdminApplyRulesPresets_form_disabled_option) ?></option>
        <optgroup label="Preset">
            <?php foreach (($RULES_PRESETS?:[]) as $PRESET_ID=>$PRESET): ?>
                <option value="<?= ($PRESET_ID) ?>"><?= ($PRESET['description']) ?></option>
            <?php endforeach; ?>
        </optgroup>
    </select>
</div>
