<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class FinancialAudit
{
    public static function record(string $subject, int $id, string $action, string $actorType, int $actorId, array $details): void
    {
        DB::table('financial_audit_events')->insert([
            'subject_type' => $subject, 'subject_id' => $id, 'action' => $action,
            'actor_type' => $actorType, 'actor_id' => $actorId,
            'details' => json_encode($details, JSON_THROW_ON_ERROR), 'created_at' => now(),
        ]);
    }
}
