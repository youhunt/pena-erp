# ERP Modules

Directory ini adalah root namespace `Modules\` untuk implementasi HMVC Pena
ERP. Modul ditambahkan per tahap pengembangan berdasarkan blueprint di
`docs/`.

Rencana module boundaries:

```text
Auth/ Dashboard/ Company/ Branch/ Users/ Roles/ Inventory/ Purchasing/
Sales/ POS/ Accounting/ HRM/ Production/ QC/ CashBank/ Reports/
Notifications/ OCR/ AI/ DocumentProcessing/ Workflow/ Settings/
```

Setiap modul yang mulai diimplementasikan menggunakan struktur minimum:

```text
Config/ Controllers/ DTO/ Entities/ Events/ Models/ Repositories/
Services/ Validation/ Views/ Database/Migrations/ Database/Seeds/
```

Pada Tahap 1 belum ada domain module yang dibuat agar tidak menghasilkan
boilerplate tanpa migration, policy, dan acceptance criteria yang pasti.
