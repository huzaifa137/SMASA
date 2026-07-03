<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
    protected $table = 'finance_transactions';

    protected $fillable = [
        'school_id', 'reference_number', 'type', 'source_type', 'source_id', 'account_id',
        'amount', 'description', 'transaction_date', 'academic_year', 'term', 'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public static function log(
        int $schoolId,
        string $type,
        string $sourceType,
        ?int $sourceId,
        float $amount,
        string $description,
        string $date,
        string $year,
        ?int $term = null,
        ?int $recordedBy = null,
        ?int $accountId = null
    ): self {
        $prefix = strtoupper(substr($type, 0, 3)) . '-' . date('Ymd') . '-';
        $last = self::where('reference_number', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $seq = $last ? ((int) substr($last->reference_number, strlen($prefix))) + 1 : 1;
        $ref = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

        return self::create([
            'school_id'        => $schoolId,
            'reference_number' => $ref,
            'type'             => $type,
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'account_id'       => $accountId,
            'amount'           => $amount,
            'description'      => $description,
            'transaction_date' => $date,
            'academic_year'    => $year,
            'term'             => $term,
            'recorded_by'      => $recordedBy,
        ]);
    }
}