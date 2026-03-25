<div id="file-type-selectors" class="settings-page choices-selector">
    <div class="field">
        <div class="control">
            <div class="selector">
                <select class="input" name="version" multiple>
                    <option placeholder disabled><?= ($AdminUsers_file_type_search_placeholder) ?></option>
                    <?php foreach (($FILE_TYPES?:[]) as $key=>$value): ?>
                        <option value="<?= ($key) ?>"><?= ($value) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>
