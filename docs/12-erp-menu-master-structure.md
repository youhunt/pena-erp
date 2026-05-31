# ERP Menu Master Structure

Dokumen ini menjadi acuan struktur menu PENA ERP berdasarkan rancangan menu enterprise.

## Setup

- Transaction Code
- Company
- Site
- Department
- Warehouse
- Location
- Country
- Province
- City
- Postal Code
- Unit of Measure
- UoM Conversion
- VAT
- Item VAT
- Address Master

Catatan: menu lama `Prefix & Numbering` diganti menjadi `Transaction Code`.

## POS

### Master
- POS Master

### Transactions
- POS System

## Sales

### Master
- Customer Master
- Customer Terms
- Customer Promo
- Customer Address

### Transactions
- Sales Order
- Allocation Order
- Delivery Order
- Sales Period Close

## Purchase

### Master
- Supplier Master
- Supplier Terms
- Supplier Promo
- Supplier Address

### Transactions
- Purchase Order
- Purchase Intransit
- Inventory Purchase Receipt
- Cost Purchase Receipt
- Purchase Period Close

## Inventory

### Master
- Item Master
- Item UoM Conversion
- Batch Master

### Transactions
- Inventory In Out
- Inventory Transfer
- Inventory Stock Opname
- Inventory Period Close

## Planning

- Forecast
- Planned Released
- MPS
- MRP

## Production

### Master
- BOM
- Work Center
- Routing

### Transactions
- Work Order
- Allocate Work Order
- Work Order In
- Work Order Out
- Work Order In Out
- Work Order Labor
- Production Period Close

## Accounts Payable

### Master
- A/P Master placeholder

### Transactions
- Manual A/P Invoice
- Purchase Invoice
- Inventory Purchase Invoice
- Advanced A/P Invoice
- Payment Invoice
- A/P Period Close

## Accounts Receivable

### Master
- A/R Master placeholder

### Transactions
- Manual A/R Invoice
- Proforma Invoice
- Sales Invoice
- Inventory Sales Invoice
- Advanced A/R Receipt
- Payment Receipt
- A/R Period Close

## Costing

### Master
- Cost Type
- Item Cost

### Transactions
- Calculate Cost

## Cash Bank

### Master
- Cash Bank ID
- Currency
- Employee ID
- Rate Master

### Transactions
- Cash Entry
- Bank Entry
- Bank Reconcile

## GL

### Master
- GL Book
- GL Column
- Account No.
- Chart of Account
- Recurring

### Transactions
- GL Entry
- Recurring Posting
- GL Period Close

## Fixed Asset / FA

### Master
- Asset ID

### Transactions
- Asset Depreciation
- Asset Period Close

## Implementation Notes

1. Existing controller/view can remain grouped by functional modules while menu labels follow this structure.
2. Import templates should follow ERP business field names, not necessarily physical database column names.
3. Customer and Supplier master templates now support multiple address purposes: office, billing, mail, and ship-to.
4. Customer/Supplier import implementation should stage full row first, then map into normalized tables after validation.
