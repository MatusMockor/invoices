<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPriceWithoutTax = $this->faker->randomFloat(2, 10, 1000);
        $taxRate = $this->faker->randomElement([20.0, 10.0, 0.0]); // Slovak VAT rates
        $discountAmount = null;

        // Calculate subtotal (quantity * unit price - discount)
        $subtotal = round($quantity * $unitPriceWithoutTax, 2);

        // Calculate VAT amount
        $taxAmount = round($subtotal * ($taxRate / 100), 2);

        // Calculate total price (subtotal + VAT)
        $totalPrice = round($subtotal + $taxAmount, 2);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(),
            'quantity' => $quantity,
            'unit_price_without_tax' => $unitPriceWithoutTax,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
        ];
    }
}
