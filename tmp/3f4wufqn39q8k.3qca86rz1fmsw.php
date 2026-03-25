<?php if (isset($SYSTEM_MESSAGES) && count($SYSTEM_MESSAGES)): ?>
    <?php foreach ($SYSTEM_MESSAGES as $id=>$message): ?>
        <div class="notification system <?= ($message['class'] ?? 'is-warning') ?>" id="system-notification-<?= ($id) ?>">
            <span class="faded">[<?= (\Wolf\Utils\ElapsedDate::short($message['created_at'])) ?>]</span>&nbsp;&nbsp;<?= ($this->raw($message['text'])) ?><button class="delete"></button>
        </div>
    <?php endforeach ?>
<?php endif; ?>
<div class="notification system is-danger is-hidden" id="client-error"></div>

