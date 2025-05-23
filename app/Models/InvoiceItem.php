<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * InvoiceItem model representing an item in an invoice.
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $description
 * @property float $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Invoice $invoice
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
