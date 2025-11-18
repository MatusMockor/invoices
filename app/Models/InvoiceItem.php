<?php

declare(strict_types=1);

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
 * @property float $unit_price_without_tax
 * @property float $tax_rate
 * @property float $tax_amount
 * @property float $subtotal
 * @property float|null $discount_amount
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
        'unit_price_without_tax',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'discount_amount',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price_without_tax' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'total_price' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
