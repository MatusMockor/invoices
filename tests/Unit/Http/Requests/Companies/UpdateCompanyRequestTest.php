<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Requests\Companies;

use App\Enums\VatPayerStatus;
use App\Http\Requests\Companies\UpdateCompanyRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

final class UpdateCompanyRequestTest extends TestCase
{
    private UpdateCompanyRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new UpdateCompanyRequest;
    }

    /**
     * Test that vat_payer_status validation accepts valid enum values.
     */
    public function test_validation_accepts_valid_vat_payer_status_values(): void
    {
        // Arrange
        $validStatuses = [
            VatPayerStatus::NOT_VAT_PAYER->value,
            VatPayerStatus::VAT_PAYER->value,
            VatPayerStatus::REGISTERED_PARAGRAPH_7A->value,
        ];

        // Act & Assert
        foreach ($validStatuses as $status) {
            $validator = Validator::make(
                [
                    'name' => fake()->company(),
                    'ico' => fake()->numerify('########'),
                    'vat_payer_status' => $status,
                ],
                $this->request->rules()
            );

            $this->assertFalse($validator->fails(), "Status '{$status}' should be valid");
        }
    }

    /**
     * Test that vat_payer_status validation rejects invalid values.
     */
    public function test_validation_rejects_invalid_vat_payer_status_values(): void
    {
        // Arrange
        $invalidStatuses = [
            'invalid_status',
            'vat_payer_invalid',
            'not_vat',
            'unknown',
            123,
        ];

        // Act & Assert
        foreach ($invalidStatuses as $status) {
            $validator = Validator::make(
                [
                    'name' => fake()->company(),
                    'ico' => fake()->numerify('########'),
                    'vat_payer_status' => $status,
                ],
                $this->request->rules()
            );

            $this->assertTrue($validator->fails(), "Status '{$status}' should be invalid");
            $this->assertTrue($validator->errors()->has('vat_payer_status'));
        }
    }

    /**
     * Test that vat_payer_status is nullable.
     */
    public function test_vat_payer_status_is_nullable(): void
    {
        // Arrange & Act
        $validator = Validator::make(
            [
                'name' => fake()->company(),
                'ico' => fake()->numerify('########'),
            ],
            $this->request->rules()
        );

        // Assert
        $this->assertFalse($validator->fails());
    }

    /**
     * Test that getVatPayerStatus() returns VatPayerStatus enum.
     */
    public function test_get_vat_payer_status_returns_enum(): void
    {
        // Arrange
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => fake()->company(),
            'ico' => fake()->numerify('########'),
            'vat_payer_status' => VatPayerStatus::VAT_PAYER->value,
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getVatPayerStatus();

        // Assert
        $this->assertInstanceOf(VatPayerStatus::class, $result);
        $this->assertSame(VatPayerStatus::VAT_PAYER, $result);
    }

    /**
     * Test that getVatPayerStatus() returns null when field is not present.
     */
    public function test_get_vat_payer_status_returns_null_when_not_present(): void
    {
        // Arrange
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => fake()->company(),
            'ico' => fake()->numerify('########'),
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getVatPayerStatus();

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test that getVatPayerStatus() correctly converts all enum values.
     */
    public function test_get_vat_payer_status_converts_all_enum_values_correctly(): void
    {
        // Arrange
        $testCases = [
            VatPayerStatus::NOT_VAT_PAYER,
            VatPayerStatus::VAT_PAYER,
            VatPayerStatus::REGISTERED_PARAGRAPH_7A,
        ];

        // Act & Assert
        foreach ($testCases as $expectedEnum) {
            $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
                'name' => fake()->company(),
                'ico' => fake()->numerify('########'),
                'vat_payer_status' => $expectedEnum->value,
            ]);

            $request->setContainer($this->app);
            $request->validateResolved();

            $result = $request->getVatPayerStatus();

            $this->assertInstanceOf(VatPayerStatus::class, $result);
            $this->assertSame($expectedEnum, $result);
        }
    }

    /**
     * Test that all required fields are validated.
     */
    public function test_validates_required_fields(): void
    {
        // Arrange & Act
        $validator = Validator::make([], $this->request->rules());

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
        $this->assertTrue($validator->errors()->has('ico'));
    }

    /**
     * Test that getName() returns correct value.
     */
    public function test_get_name_returns_correct_value(): void
    {
        // Arrange
        $companyName = fake()->company();
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => $companyName,
            'ico' => fake()->numerify('########'),
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getName();

        // Assert
        $this->assertSame($companyName, $result);
    }

    /**
     * Test that getIco() returns correct value.
     */
    public function test_get_ico_returns_correct_value(): void
    {
        // Arrange
        $ico = fake()->numerify('########');
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => fake()->company(),
            'ico' => $ico,
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getIco();

        // Assert
        $this->assertSame($ico, $result);
    }

    /**
     * Test that getIcDph() returns correct value when present.
     */
    public function test_get_ic_dph_returns_correct_value_when_present(): void
    {
        // Arrange
        $icDph = 'SK'.fake()->numerify('##########');
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => fake()->company(),
            'ico' => fake()->numerify('########'),
            'ic_dph' => $icDph,
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getIcDph();

        // Assert
        $this->assertSame($icDph, $result);
    }

    /**
     * Test that getIcDph() returns null when not present.
     */
    public function test_get_ic_dph_returns_null_when_not_present(): void
    {
        // Arrange
        $request = UpdateCompanyRequest::create('/companies/1', 'PUT', [
            'name' => fake()->company(),
            'ico' => fake()->numerify('########'),
        ]);

        $request->setContainer($this->app);
        $request->validateResolved();

        // Act
        $result = $request->getIcDph();

        // Assert
        $this->assertNull($result);
    }
}
