<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'message',
        'source',
        'attachment',
        'department',
        'priority',
        'order_number',
        'status',
        // أضف الحقول المالية الجديدة هنا للسماح بحفظها
        'amount',
        'document_date',
        'invoice_number',
        'ai_analysis',
    ];

    protected function casts(): array
    {
        return [
            'ai_analysis' => 'array',
            'document_date' => 'date',
        ];
    }
}