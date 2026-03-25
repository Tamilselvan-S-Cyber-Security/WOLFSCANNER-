<?php

namespace Wolf\Models;

class EventType extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_type';

    public function getAll(): array {
        $query = 'SELECT id, value, name FROM event_type';

        return $this->execQuery($query, null);
    }
}
