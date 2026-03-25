<div id="event-type-selectors" class="settings-page choices-selector">
    <div class="field">
        <div class="control">
            <div class="selector">
                <select class="input" name="version" multiple>
                    <option placeholder disabled><?= ($AdminEvents_event_type_search_placeholder) ?></option>
                    <?php foreach (($EVENT_TYPES?:[]) as $record): ?>
                        <option value="<?= ($record['id']) ?>"><?= ($record['value']) ?>|<?= ($record['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>
