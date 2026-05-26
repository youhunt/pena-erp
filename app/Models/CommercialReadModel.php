<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

final class CommercialReadModel extends Model
{
    protected $table = 'customers';

    /** @return list<array<string, mixed>> */
    public function customers(int $companyId): array
    {
        return $this->db->table('customers p')
            ->select('p.*, c.code AS currency_code, t.code AS term_code')
            ->join('currencies c', 'c.id = p.currency_id AND c.company_id = p.company_id')
            ->join('customer_terms t', 't.id = p.default_term_id AND t.company_id = p.company_id', 'left')
            ->where('p.company_id', $companyId)->where('p.deleted_at', null)->orderBy('p.name', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function suppliers(int $companyId): array
    {
        return $this->db->table('suppliers p')
            ->select('p.*, c.code AS currency_code, t.code AS term_code')
            ->join('currencies c', 'c.id = p.currency_id AND c.company_id = p.company_id')
            ->join('supplier_terms t', 't.id = p.default_term_id AND t.company_id = p.company_id', 'left')
            ->where('p.company_id', $companyId)->where('p.deleted_at', null)->orderBy('p.name', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function customerTerms(int $companyId): array
    {
        return $this->tenantRows('customer_terms', $companyId, 'code');
    }

    /** @return list<array<string, mixed>> */
    public function supplierTerms(int $companyId): array
    {
        return $this->tenantRows('supplier_terms', $companyId, 'code');
    }

    /** @return list<array<string, mixed>> */
    public function customerPromotions(int $companyId): array
    {
        return $this->promotions('customer_promotions', 'customer_id', 'customers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function supplierPromotions(int $companyId): array
    {
        return $this->promotions('supplier_promotions', 'supplier_id', 'suppliers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function customerAddresses(int $companyId): array
    {
        return $this->partnerAddresses('customer_addresses', 'customer_id', 'customers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function supplierAddresses(int $companyId): array
    {
        return $this->partnerAddresses('supplier_addresses', 'supplier_id', 'suppliers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function customerProfiles(int $companyId): array
    {
        return $this->profiles('customer_profiles', 'customer_id', 'customers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function supplierProfiles(int $companyId): array
    {
        return $this->profiles('supplier_profiles', 'supplier_id', 'suppliers', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function currencies(int $companyId): array
    {
        return $this->db->table('currencies')->select('id, code, name')->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function addresses(int $companyId): array
    {
        return $this->db->table('addresses')->select('id, code, label')->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)->orderBy('label', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function taxCodes(int $companyId): array
    {
        return $this->db->table('tax_codes')->select('id, code, name, rate')->where(['company_id' => $companyId, 'status' => 'active'])->where('deleted_at', null)->orderBy('code', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function warehouses(int $companyId): array
    {
        return $this->db->table('warehouses w')
            ->select('w.id, w.code, w.name, b.code AS branch_code')
            ->join('branches b', 'b.id = w.branch_id AND b.company_id = w.company_id')
            ->where(['w.company_id' => $companyId, 'w.is_active' => true])
            ->where('w.deleted_at', null)->orderBy('b.code', 'ASC')->orderBy('w.code', 'ASC')->get()->getResultArray();
    }

    public function codeExists(string $table, int $companyId, string $code): bool
    {
        return $this->db->table($table)->where(['company_id' => $companyId, 'code' => $code])->where('deleted_at', null)->countAllResults() > 0;
    }

    public function addressLinkExists(string $table, string $partnerId, int $companyId, int $partner, int $addressId, string $addressType): bool
    {
        return $this->db->table($table)
            ->where(['company_id' => $companyId, $partnerId => $partner, 'address_id' => $addressId, 'address_type' => $addressType])
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    /** @return list<array<string, mixed>> */
    private function tenantRows(string $table, int $companyId, string $orderBy): array
    {
        return $this->db->table($table)->where('company_id', $companyId)->where('deleted_at', null)->orderBy($orderBy, 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function promotions(string $table, string $partnerId, string $partnerTable, int $companyId): array
    {
        return $this->db->table($table . ' x')
            ->select('x.*, p.code AS partner_code, p.name AS partner_name')
            ->join($partnerTable . ' p', "p.id = x.{$partnerId} AND p.company_id = x.company_id", 'left')
            ->where('x.company_id', $companyId)->where('x.deleted_at', null)->orderBy('x.starts_on', 'DESC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function partnerAddresses(string $table, string $partnerId, string $partnerTable, int $companyId): array
    {
        return $this->db->table($table . ' x')
            ->select('x.*, p.code AS partner_code, p.name AS partner_name, a.code AS address_code, a.label AS address_label')
            ->join($partnerTable . ' p', "p.id = x.{$partnerId} AND p.company_id = x.company_id")
            ->join('addresses a', 'a.id = x.address_id AND a.company_id = x.company_id')
            ->where('x.company_id', $companyId)->where('x.deleted_at', null)->orderBy('p.name', 'ASC')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    private function profiles(string $table, string $partnerId, string $partnerTable, int $companyId): array
    {
        return $this->db->table($table . ' x')
            ->select('x.*, p.code AS partner_code, p.name AS partner_name, t.code AS tax_code, w.code AS warehouse_code')
            ->join($partnerTable . ' p', "p.id = x.{$partnerId} AND p.company_id = x.company_id")
            ->join('tax_codes t', 't.id = x.default_tax_code_id AND t.company_id = x.company_id', 'left')
            ->join('warehouses w', 'w.id = x.default_warehouse_id AND w.company_id = x.company_id', 'left')
            ->where('x.company_id', $companyId)->where('x.deleted_at', null)->orderBy('p.name', 'ASC')->get()->getResultArray();
    }
}
