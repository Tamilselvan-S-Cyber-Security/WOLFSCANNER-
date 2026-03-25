<?php

declare(strict_types=1);

namespace Wolf\Models\Chart;

class RiskCorrelation extends Base {
    protected ?string $DB_TABLE_NAME = 'event_account';

    public function getData(int $apiKey): array {
        $field1 = 'avg_risk_score';
        $data1  = $this->getAvgScoreLine($apiKey);

        $field2 = 'events_count';
        $data2  = $this->getEventsCountLine($apiKey);

        $data0 = $this->concatDataLines($data1, $field1, $data2, $field2);

        $indexedData = array_values($data0);
        $timestamps  = array_column($indexedData, 'ts');
        $line1       = array_column($indexedData, $field1);
        $line2       = array_column($indexedData, $field2);

        return $this->addEmptyDays([$timestamps, $line1, $line2]);
    }

    private function getAvgScoreLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.score_updated_at + :offset))::bigint AS ts,
                COALESCE(AVG(event_account.score), 0) AS avg_risk_score

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.score_updated_at IS NOT NULL AND
                event_account.score_updated_at >= :start_time AND
                event_account.score_updated_at <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }

    private function getEventsCountLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(event.id) AS events_count

            FROM
                event

            WHERE
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
