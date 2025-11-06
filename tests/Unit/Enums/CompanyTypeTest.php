<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\CompanyType;
use Tests\TestCase;

final class CompanyTypeTest extends TestCase
{
    /**
     * Test that all prefixes map correctly to their company types.
     */
    public function test_from_prefix_maps_all_prefixes_correctly(): void
    {
        // Arrange & Act & Assert
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('Sa/'));
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('Sro/'));
        $this->assertSame(CompanyType::COOPERATIVE, CompanyType::fromPrefix('Dr/'));
        $this->assertSame(CompanyType::AGRICULTURAL_COOPERATIVE, CompanyType::fromPrefix('Po/'));
        $this->assertSame(CompanyType::FOUNDATION, CompanyType::fromPrefix('N/'));
        $this->assertSame(CompanyType::MUNICIPALITY, CompanyType::fromPrefix('Ob/'));
        $this->assertSame(CompanyType::GENERAL_PARTNERSHIP, CompanyType::fromPrefix('Vo/'));
        $this->assertSame(CompanyType::LIMITED_PARTNERSHIP, CompanyType::fromPrefix('Ks/'));
        $this->assertSame(CompanyType::EUROPEAN_ECONOMIC_INTEREST_GROUPING, CompanyType::fromPrefix('Ez/'));
        $this->assertSame(CompanyType::CONDOMINIUM_ASSOCIATION, CompanyType::fromPrefix('Sz/'));
        $this->assertSame(CompanyType::SPORTS_ORGANIZATION, CompanyType::fromPrefix('Sp/'));
        $this->assertSame(CompanyType::POLITICAL_PARTY, CompanyType::fromPrefix('Pc/'));
        $this->assertSame(CompanyType::CIVIC_ASSOCIATION, CompanyType::fromPrefix('Oc/'));
    }

    /**
     * Test that prefix matching is case-insensitive.
     */
    public function test_from_prefix_is_case_insensitive(): void
    {
        // Arrange & Act & Assert - Test Sa/ in different cases
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('SA/'));
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('sa/'));
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('Sa/'));
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('sA/'));

        // Test Sro/ in different cases
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('SRO/'));
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('sro/'));
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('Sro/'));
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('SrO/'));

        // Test Dr/ in different cases
        $this->assertSame(CompanyType::COOPERATIVE, CompanyType::fromPrefix('DR/'));
        $this->assertSame(CompanyType::COOPERATIVE, CompanyType::fromPrefix('dr/'));
        $this->assertSame(CompanyType::COOPERATIVE, CompanyType::fromPrefix('Dr/'));
    }

    /**
     * Test that unknown prefixes return null.
     */
    public function test_from_prefix_returns_null_for_unknown_prefix(): void
    {
        // Arrange & Act & Assert
        $this->assertNull(CompanyType::fromPrefix('XYZ/'));
        $this->assertNull(CompanyType::fromPrefix('Unknown/'));
        $this->assertNull(CompanyType::fromPrefix('ABC/'));
        $this->assertNull(CompanyType::fromPrefix('123/'));
    }

    /**
     * Test that empty string returns null.
     */
    public function test_from_prefix_returns_null_for_empty_string(): void
    {
        // Arrange & Act & Assert
        $this->assertNull(CompanyType::fromPrefix(''));
    }

    /**
     * Test that prefix without slash returns null.
     */
    public function test_from_prefix_returns_null_for_prefix_without_slash(): void
    {
        // Arrange & Act & Assert
        $this->assertNull(CompanyType::fromPrefix('Sa'));
        $this->assertNull(CompanyType::fromPrefix('Sro'));
        $this->assertNull(CompanyType::fromPrefix('Dr'));
    }

    /**
     * Test that fromPrefix works with full registration numbers (not just prefixes).
     */
    public function test_from_prefix_works_with_full_registration_numbers(): void
    {
        // The fromPrefix method should extract prefix from full registration numbers
        $this->assertSame(CompanyType::JOINT_STOCK_COMPANY, CompanyType::fromPrefix('Sa/6266/B'));
        $this->assertSame(CompanyType::LIMITED_LIABILITY_COMPANY, CompanyType::fromPrefix('Sro/81134/B'));
        $this->assertSame(CompanyType::COOPERATIVE, CompanyType::fromPrefix('Dr/1852/B'));
        $this->assertSame(CompanyType::AGRICULTURAL_COOPERATIVE, CompanyType::fromPrefix('Po/1158/B'));
    }

    /**
     * Test enum values are in the correct format.
     */
    public function test_enum_values_are_lowercase_snake_case(): void
    {
        // Arrange & Act & Assert
        $this->assertSame('joint_stock_company', CompanyType::JOINT_STOCK_COMPANY->value);
        $this->assertSame('limited_liability_company', CompanyType::LIMITED_LIABILITY_COMPANY->value);
        $this->assertSame('cooperative', CompanyType::COOPERATIVE->value);
        $this->assertSame('agricultural_cooperative', CompanyType::AGRICULTURAL_COOPERATIVE->value);
        $this->assertSame('foundation', CompanyType::FOUNDATION->value);
        $this->assertSame('municipality', CompanyType::MUNICIPALITY->value);
        $this->assertSame('general_partnership', CompanyType::GENERAL_PARTNERSHIP->value);
        $this->assertSame('limited_partnership', CompanyType::LIMITED_PARTNERSHIP->value);
        $this->assertSame('european_economic_interest_grouping', CompanyType::EUROPEAN_ECONOMIC_INTEREST_GROUPING->value);
        $this->assertSame('condominium_association', CompanyType::CONDOMINIUM_ASSOCIATION->value);
        $this->assertSame('sports_organization', CompanyType::SPORTS_ORGANIZATION->value);
        $this->assertSame('political_party', CompanyType::POLITICAL_PARTY->value);
        $this->assertSame('civic_association', CompanyType::CIVIC_ASSOCIATION->value);
    }

    /**
     * Test all 13 company types exist.
     */
    public function test_has_all_thirteen_company_types(): void
    {
        // Arrange & Act
        $allCases = CompanyType::cases();

        // Assert
        $this->assertCount(13, $allCases);
    }
}
