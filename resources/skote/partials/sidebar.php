<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu"><?= lang('Files.Menu') ?></li>

                <li>
                <a href="/" class="waves-effect">
                        <i class="bx bx-home-circle"></i>
                        <span key="t-dashboards"><?= lang('Files.Dashboards') ?></span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <!-- <span class="badge rounded-pill bg-danger float-end" key="t-setup"><?= lang('Setup') ?></span> -->
                        <i class="bx bx-cog"></i>
                        <span key="t-setup"><?= lang('Files.Setup') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/transactioncode" key="t-transactioncode"><?= lang('Files.TransactionCode') ?></a></li>
                        <li><a href="/company" key="t-company"><?= lang('Files.Company') ?></a></li>
                        <li><a href="/site" key="t-site"><?= lang('Files.Site') ?></a></li>
                        <li><a href="/department" key="t-department"><?= lang('Files.Department') ?></a></li>
                        <li><a href="/warehouse" key="t-warehouse"><?= lang('Files.Warehouse') ?></a></li>
                        <li><a href="/location" key="t-location"><?= lang('Files.Location') ?></a></li>
                        <li><a href="/countries" key="t-countries"><?= lang('Files.Countries') ?></a></li>
                        <li><a href="/provinces" key="t-provinces"><?= lang('Files.Provinces') ?></a></li>
                        <li><a href="/cities" key="t-cities"><?= lang('Files.Cities') ?></a></li>
                        <li><a href="/postalcode" key="t-postalcode"><?= lang('Files.PostalCode') ?></a></li>
                        <li><a href="/uom" key="t-uom"><?= lang('Files.UOM') ?></a></li>
                        <li><a href="/convuom" key="t-convuom"><?= lang('Files.ConvUOM') ?></a></li>
                        <li><a href="/vat" key="t-vat"><?= lang('Files.VAT') ?></a></li>
                        <li><a href="/itemvat" key="t-itemvat"><?= lang('Files.ItemVAT') ?></a></li>
                        <li><a href="/addressmaster" key="t-addressmaster"><?= lang('Files.AddressMaster') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-store"></i>
                        <span key="t-inventory"><?= lang('Files.Sales') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/customermaster" key="t-customermaster"><?= lang('Files.CustomerMaster') ?></a></li>
                                <li><a href="/customerterms" key="t-customerterms"><?= lang('Files.CustomerTerms') ?></a></li>
                                <li><a href="/customerpromo" key="t-customerpromo"><?= lang('Files.CustomerPromo') ?></a></li>
                                <li><a href="/customeraddress" key="t-customeraddress"><?= lang('Files.CustomerAddress') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/transactions" key="t-transactions"><?= lang('Files.Transactions') ?></a></li>
                                <li><a href="/salesorder" key="t-salesorder"><?= lang('Files.SalesOrder') ?></a></li>
                                <li><a href="/allocationorder" key="t-allocationorder"><?= lang('Files.AllocationOrder') ?></a></li>
                                <li><a href="/deliveryorder" key="t-deliveryorder"><?= lang('Files.DeliveryOrder') ?></a></li>
                                <li><a href="/salesperiodclose" key="t-salesperiodclose"><?= lang('Files.SalesPeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-store"></i>
                        <span key="t-inventory"><?= lang('Files.Purchase') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/suppliermaster" key="t-suppliermaster"><?= lang('Files.SupplierMaster') ?></a></li>
                                <li><a href="/supplierterms" key="t-supplierterms"><?= lang('Files.SupplierTerms') ?></a></li>
                                <li><a href="/supplierpromo" key="t-supplierpromo"><?= lang('Files.SupplierPromo') ?></a></li>
                                <li><a href="/supplieraddress" key="t-supplieraddress"><?= lang('Files.SupplierAddress') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/transactions" key="t-transactions"><?= lang('Files.Transactions') ?></a></li>
                                <li><a href="/purchaseorder" key="t-purchaseorder"><?= lang('Files.PurchaseOrder') ?></a></li>
                                <li><a href="/purchaseintransit" key="t-purchaseintransit"><?= lang('Files.PurchaseIntransit') ?></a></li>
                                <li><a href="/inventorypurchasereceipt" key="t-inventorypurchasereceipt"><?= lang('Files.InventoryPurchaseReceipt') ?></a></li>
                                <li><a href="/costpurchasereceipt" key="t-costpurchasereceipt"><?= lang('Files.CostPurchaseReceipt') ?></a></li>
                                <li><a href="/purchaseperiodclose" key="t-purchaseperiodclose"><?= lang('Files.PurchasePeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-store"></i>
                        <span key="t-inventory"><?= lang('Files.Inventory') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/item" key="t-item"><?= lang('Files.Item') ?></a></li>
                                <li><a href="/itmuomconv" key="t-itmuomconv"><?= lang('Files.ItmUOMConv') ?></a></li>
                                <li><a href="/batchmaster" key="t-batchmaster"><?= lang('Files.BatchMaster') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/invtrans" key="t-invtrans"><?= lang('Files.InvTrans') ?></a></li>
                                <li><a href="/invtranfer" key="t-invtranfer"><?= lang('Files.InvTranfer') ?></a></li>
                                <li><a href="/invstockopname" key="t-invstockopname"><?= lang('Files.InvStockOpname') ?></a></li>
                                <li><a href="/inventoryperiodclose" key="t-inventoryperiodclose"><?= lang('Files.InventoryPeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-calendar"></i>
                        <span key="t-planning"><?= lang('Files.Planning') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="/forecast" key="t-forecast"><?= lang('Files.Forecast') ?></a></li>
                        <li><a href="/plannedreleased" key="t-plannedreleased"><?= lang('Files.PlannedReleased') ?></a></li>
                        <li><a href="/mps" key="t-mps"><?= lang('Files.MPS') ?></a></li>
                        <li><a href="/mrp" key="t-mrp"><?= lang('Files.MRP') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-detail"></i>
                        <span key="t-production"><?= lang('Files.Production') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/bom" key="t-bom"><?= lang('Files.BOM') ?></a></li>
                                <li><a href="/work_center" key="t-workcenter"><?= lang('Files.WorkCenter') ?></a></li>
                                <li><a href="/routing" key="t-routing"><?= lang('Files.Routing') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/workorder" key="t-workorder"><?= lang('Files.WorkOrder') ?></a></li>
                                <li><a href="/allocateworkorder" key="t-allocateworkorder"><?= lang('Files.AllocateWorkOrder') ?></a></li>
                                <li><a href="/workorderin" key="t-workorderin"><?= lang('Files.WorkOrderIn') ?></a></li>
                                <li><a href="/workorderout" key="t-workorderout"><?= lang('Files.WorkOrderOut') ?></a></li>
                                <li><a href="/workorderinout" key="t-workorderinout"><?= lang('Files.WorkOrderInOut') ?></a></li>
                                <li><a href="/workorderlabor" key="t-workorderlabor"><?= lang('Files.WorkOrderLabor') ?></a></li>
                                <li><a href="/productionperiodclose" key="t-productionperiodclose"><?= lang('Files.ProductionPeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-gl"><?= lang('Files.AccountsPayable') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                            </ul>
                        </li>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/manualapinvoice" key="t-manualapinvoice"><?= lang('Files.ManualAPInvoice') ?></a></li>
                                <li><a href="/purchaseinvoice" key="t-purchaseinvoice"><?= lang('Files.PurchaseInvoice') ?></a></li>
                                <li><a href="/inventorypurchaseinvoice" key="t-inventorypurchaseinvoice"><?= lang('Files.InventoryPurchaseInvoice') ?></a></li>
                                <li><a href="/advancedapinvoice" key="t-advancedapinvoice"><?= lang('Files.AdvancedAPInvoice') ?></a></li>
                                <li><a href="/paymentinvoice" key="t-paymentinvoice"><?= lang('Files.PaymentInvoice') ?></a></li>
                                <li><a href="/apperiodclose" key="t-apperiodclose"><?= lang('Files.APPeriodClose') ?></a></li>

                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-gl"><?= lang('Files.AccountsReceivable') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                            </ul>
                        </li>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/manualarinvoice" key="t-manualarinvoice"><?= lang('Files.ManualARInvoice') ?></a></li>
                                <li><a href="/proformainvoice" key="t-proformainvoice"><?= lang('Files.ProformaInvoice') ?></a></li>
                                <li><a href="/salesinvoice" key="t-salesinvoice"><?= lang('Files.SalesInvoice') ?></a></li>
                                <li><a href="/inventorysalesinvoice" key="t-inventorysalesinvoice"><?= lang('Files.InventorySalesInvoice') ?></a></li>
                                <li><a href="/advancedarreceipt" key="t-advancedarreceipt"><?= lang('Files.AdvancedARReceipt') ?></a></li>
                                <li><a href="/paymentreceipt" key="t-paymentreceipt"><?= lang('Files.PaymentReceipt') ?></a></li>
                                <li><a href="/arperiodclose" key="t-arperiodclose"><?= lang('Files.ARPeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-dollar"></i>
                        <span key="t-costing"><?= lang('Files.Costing') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/costtype" key="t-costtype"><?= lang('Files.CostType') ?></a></li>
                                <li><a href="/itemcost" key="t-itemcost"><?= lang('Files.ItemCost') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/calculatecost" key="t-calculatecost"><?= lang('Files.CalculateCost') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-gl"><?= lang('Files.CashBank') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/cashbankid" key="t-cashbankid"><?= lang('Files.CashBankID') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/cashentry" key="t-cashentry"><?= lang('Files.CashEntry') ?></a></li>
                                <li><a href="/bankentry" key="t-bankentry"><?= lang('Files.BankEntry') ?></a></li>
                                <li><a href="/bankreconcile" key="t-bankreconcile"><?= lang('Files.BankReconcile') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-gl"><?= lang('Files.GL') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master"><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/column" key="t-column"><?= lang('Files.Column') ?></a></li>
                                <li><a href="/accountno" key="t-accountno"><?= lang('Files.AccountNo') ?></a></li>
                                <li><a href="/chartofaccount" key="t-chartofaccount"><?= lang('Files.ChartofAccount') ?></a></li>
                                <li><a href="/recurring" key="t-recurring"><?= lang('Files.Recurring') ?></a></li>
                            </ul>
                        </li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-transactions"><?= lang('Files.Transactions') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/gltransactions" key="t-gltransactions"><?= lang('Files.GLTransactions') ?></a></li>
                                <li><a href="/glperiodclose" key="t-glperiodclose"><?= lang('Files.GLPeriodClose') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <!-- <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-share-alt"></i>
                        <span key="t-inventory"><?= lang('Files.Inventory') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-master><?= lang('Files.Master') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="/item" key="t-item"><?= lang('Files.Item') ?></a></li>
                                <li><a href="javascript: void(0);" key="t-level-2-2"><?= lang('Files.Level') ?> 2.2</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-share-alt"></i>
                        <span key="t-inventory"><?= lang('Files.Inventory') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li class="menu-title" key="t-master"><a href="javascript: void(0);"><?= lang('Files.Master') ?></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-level-1-2"><?= lang('Files.Level') ?> 1.2</a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="javascript: void(0);" key="t-level-2-1"><?= lang('Files.Level') ?> 2.1</a></li>
                                <li><a href="javascript: void(0);" key="t-level-2-2"><?= lang('Files.Level') ?> 2.2</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-share-alt"></i>
                        <span key="t-multi-level"><?= lang('Files.Multi Level') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="javascript: void(0);" key="t-level-1-1"><?= lang('Files.Level') ?> 1.1</a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-level-1-2"><?= lang('Files.Level') ?> 1.2</a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="javascript: void(0);" key="t-level-2-1"><?= lang('Files.Level') ?> 2.1</a></li>
                                <li><a href="javascript: void(0);" key="t-level-2-2"><?= lang('Files.Level') ?> 2.2</a></li>
                            </ul>
                        </li>
                    </ul>
                </li> -->

                <!-- <li class="menu-title" key="t-apps"><?= lang('Files.Apps') ?></li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-calendar"></i>
                        <span key="t-dashboards"><?= lang('Files.Calendars') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="calendar" key="t-tui-calendar"><?= lang('Files.TUI_Calendar') ?></a></li>
                        <li><a href="calendar-full" key="t-full-calendar"><?= lang('Files.Full_Calendar') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="chat" class="waves-effect">
                        <i class="bx bx-chat"></i>
                        <span key="t-chat"><?= lang('Files.Chat') ?></span>
                    </a>
                </li>

                <li>
                    <a href="apps-filemanager" class="waves-effect">
                        <i class="bx bx-file"></i>
                        
                        <span key="t-file-manager"><?= lang('Files.File Manager') ?></span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-store"></i>
                        <span key="t-ecommerce"><span><?= lang('Files.Ecommerce') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ecommerce-products" key="t-products"><?= lang('Files.Products') ?></a></li>
                        <li><a href="ecommerce-product-detail" key="t-product-detail"><?= lang('Files.Product Detail') ?></a></li>
                        <li><a href="ecommerce-orders" key="t-orders"><?= lang('Files.Orders') ?></a></li>
                        <li><a href="ecommerce-customers" key="t-customers"><?= lang('Files.Customers') ?></a></li>
                        <li><a href="ecommerce-cart" key="t-cart"><?= lang('Files.Cart') ?></a></li>
                        <li><a href="ecommerce-checkout" key="t-checkout"><?= lang('Files.Checkout') ?></a></li>
                        <li><a href="ecommerce-shops" key="t-shops"><?= lang('Files.Shops') ?></a></li>
                        <li><a href="ecommerce-add-product" key="t-add-product"><?= lang('Files.Add Product') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-bitcoin"></i>
                        <span key="t-crypto"><?= lang('Files.Crypto') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="crypto-wallet" key="t-wallet"><?= lang('Files.Wallet') ?></a></li>
                        <li><a href="crypto-buy-sell" key="t-buy"><?= lang('Files.Buy/Sell') ?></a></li>
                        <li><a href="crypto-exchange" key="t-exchange"><?= lang('Files.Exchange') ?></a></li>
                        <li><a href="crypto-lending" key="t-lending"><?= lang('Files.Lending') ?></a></li>
                        <li><a href="crypto-orders" key="t-orders"><?= lang('Files.Orders') ?></a></li>
                        <li><a href="crypto-kyc-application" key="t-kyc"><?= lang('Files.KYC Application') ?></a></li>
                        <li><a href="crypto-ico-landing" key="t-ico"><?= lang('Files.ICO Landing') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-envelope"></i>
                        <span key="t-email"><?= lang('Files.Email') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="email-inbox" key="t-inbox"><?= lang('Files.Inbox') ?></a></li>
                        <li><a href="email-read" key="t-read-email"><?= lang('Files.Read Email') ?></a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                
                                <span key="t-email-templates"><?= lang('Files.Templates') ?></span>
                            </a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="email-template-basic" key="t-basic-action"><?= lang('Files.Basic Action') ?></a></li>
                                <li><a href="email-template-alert" key="t-alert-email"><?= lang('Files.Alert Email') ?></a></li>
                                <li><a href="email-template-billing" key="t-bill-email"><?= lang('Files.Billing Email') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-receipt"></i>
                        <span key="t-invoices"><?= lang('Files.Invoices') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="invoices-list" key="t-invoice-list"><?= lang('Files.Invoice List') ?></a></li>
                        <li><a href="invoices-detail" key="t-invoice-detail"><?= lang('Files.Invoice Detail') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-briefcase-alt-2"></i>
                        <span key="t-projects"><?= lang('Files.Projects') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="projects-grid" key="t-p-grid"><?= lang('Files.Projects Grid') ?></a></li>
                        <li><a href="projects-list" key="t-p-list"><?= lang('Files.Projects List') ?></a></li>
                        <li><a href="projects-overview" key="t-p-overview"><?= lang('Files.Project Overview') ?></a></li>
                        <li><a href="projects-create" key="t-create-new"><?= lang('Files.Create New') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-task"></i>
                        <span key="t-tasks"><?= lang('Files.Tasks') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tasks-list" key="t-task-list"><?= lang('Files.Task List') ?></a></li>
                        <li><a href="tasks-kanban" key="t-kanban-board"><?= lang('Files.Kanban Board') ?></a></li>
                        <li><a href="tasks-create" key="t-create-task"><?= lang('Files.Create Task') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bxs-user-detail"></i>
                        <span key="t-contacts"><?= lang('Files.Contacts') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="contacts-grid" key="t-user-grid"><?= lang('Files.Users Grid') ?></a></li>
                        <li><a href="contacts-list" key="t-user-list"><?= lang('Files.Users List') ?></a></li>
                        <li><a href="contacts-profile" key="t-profile"><?= lang('Files.Profile') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect has-arrow">
                        <i class="bx bx-detail"></i>
                        <span key="t-blog"><?= lang('Files.Blog') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="blog-list" key="t-blog-list"><?= lang('Files.Blog List') ?></a></li>
                        <li><a href="blog-grid" key="t-blog-grid"><?= lang('Files.Blog Grid') ?></a></li>
                        <li><a href="blog-details" key="t-blog-details"><?= lang('Files.Blog Details') ?></a></li>
                    </ul>
                </li>

                
                <li>
                    <a href="javascript: void(0);" class="waves-effect has-arrow">
                        <i class="bx bx-briefcase-alt"></i>
                        <span key="t-jobs"><?= lang('Files.Job') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="job-list" key="t-job-list"><?= lang('Files.Job List') ?></a></li>
                        <li><a href="job-grid" key="t-job-grid"><?= lang('Files.Job Grid') ?></a></li>
                        <li><a href="job-apply" key="t-apply-job"><?= lang('Files.Apply Job') ?></a></li>
                        <li><a href="job-details" key="t-job-details"><?= lang('Files.Job Details') ?></a></li>
                        <li><a href="job-categories" key="t-Jobs-categories"><?= lang('Files.Job Categories') ?></a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-candidate"><?= lang('Files.Candidate') ?></a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="candidate-list" key="t-list"><?= lang('Files.List') ?></a></li>
                                <li><a href="candidate-overview" key="t-overview"><?= lang('Files.Overview') ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                
                <li class="menu-title" key="t-pages"><?= lang('Files.Pages') ?></li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect  has-arrow">
                        <i class="bx bx-user-circle"></i>
                        <span key="t-authentication"><?= lang('Files.Authentication') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="auth-login" key="t-login"><?= lang('Files.Login') ?></a></li>
                        <li><a href="auth-login-2" key="t-login-2"><?= lang('Files.Login') ?> 2</a></li>
                        <li><a href="auth-register" key="t-register"><?= lang('Files.Register') ?></a></li>
                        <li><a href="auth-register-2" key="t-register-2"><?= lang('Files.Register') ?> 2</a></li>
                        <li><a href="auth-recoverpw" key="t-recover-password"><?= lang('Files.Recover Password') ?></a></li>
                        <li><a href="auth-recoverpw-2" key="t-recover-password-2"><?= lang('Files.Recover Password') ?> 2</a></li>
                        <li><a href="auth-lock-screen" key="t-lock-screen"><?= lang('Files.Lock Screen') ?></a></li>
                        <li><a href="auth-lock-screen-2" key="t-lock-screen-2"><?= lang('Files.Lock Screen') ?> 2</a></li>
                        <li><a href="auth-confirm-mail" key="t-confirm-mail"><?= lang('Files.Confirm Mail') ?></a></li>
                        <li><a href="auth-confirm-mail-2" key="t-confirm-mail-2"><?= lang('Files.Confirm Mail') ?> 2</a></li>
                        <li><a href="auth-email-verification" key="t-email-verification"><?= lang('Files.Email Verification') ?></a></li>
                        <li><a href="auth-email-verification-2" key="t-email-verification-2"><?= lang('Files.Email Verification') ?> 2</a></li>
                        <li><a href="auth-two-step-verification" key="t-two-step-verification"><?= lang('Files.Two Step Verification') ?></a></li>
                        <li><a href="auth-two-step-verification-2" key="t-two-step-verification-2"><?= lang('Files.Two Step Verification') ?> 2</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-file"></i>
                        <span key="t-utility"><?= lang('Files.Utility') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="pages-starter" key="t-starter-page"><?= lang('Files.Starter Page') ?></a></li>
                        <li><a href="pages-maintenance" key="t-maintenance"><?= lang('Files.Maintenance') ?></a></li>
                        <li><a href="pages-comingsoon" key="t-coming-soon"><?= lang('Files.Coming Soon') ?></a></li>
                        <li><a href="pages-timeline" key="t-timeline"><?= lang('Files.Timeline') ?></a></li>
                        <li><a href="pages-faqs" key="t-faqs"><?= lang('Files.FAQs') ?></a></li>
                        <li><a href="pages-pricing" key="t-pricing"><?= lang('Files.Pricing') ?></a></li>
                        <li><a href="pages-404" key="t-error-404"><?= lang('Files.Error') ?> 404</a></li>
                        <li><a href="pages-500" key="t-error-500"><?= lang('Files.Error') ?> 500</a></li>
                    </ul>
                </li>

                <li class="menu-title" key="t-components"><?= lang('Files.Components') ?></li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-tone"></i>
                        <span key="t-ui-elements"><?= lang('Files.UI Elements') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="ui-alerts" key="t-alerts"><?= lang('Files.Alerts') ?></a></li>
                        <li><a href="ui-buttons" key="t-buttons"><?= lang('Files.Buttons') ?></a></li>
                        <li><a href="ui-cards" key="t-cards"><?= lang('Files.Cards') ?></a></li>
                        <li><a href="ui-carousel" key="t-carousel"><?= lang('Files.Carousel') ?></a></li>
                        <li><a href="ui-dropdowns" key="t-dropdowns"><?= lang('Files.Dropdowns') ?></a></li>
                        <li><a href="ui-grid" key="t-grid"><?= lang('Files.Grid') ?></a></li>
                        <li><a href="ui-images" key="t-images"><?= lang('Files.Images') ?></a></li>
                        <li><a href="ui-lightbox" key="t-lightbox"><?= lang('Files.Lightbox') ?></a></li>
                        <li><a href="ui-modals" key="t-modals"><?= lang('Files.Modals') ?></a></li>
                        <li><a href="ui-offcanvas" key="t-offcanvas"><?= lang('Files.Offcanvas') ?></a></li>
                        <li><a href="ui-rangeslider" key="t-range-slider"><?= lang('Files.Range Slider') ?></a></li>
                        <li><a href="ui-session-timeout" key="t-session-timeout"><?= lang('Files.Session Timeout') ?></a></li>
                        <li><a href="ui-progressbars" key="t-progress-bars"><?= lang('Files.Progress Bars') ?></a></li>
                        <li><a href="ui-placeholders" key="t-placeholders"><?= lang('Files.Placeholders') ?></a></li>
                        <li><a href="ui-sweet-alert" key="t-sweet-alert"><?= lang('Files.Sweet-Alert') ?></a></li>
                        <li><a href="ui-tabs-accordions" key="t-tabs-accordions"><?= lang('Files.Tabs & Accordions') ?></a></li>
                        <li><a href="ui-typography" key="t-typography"><?= lang('Files.Typography') ?></a></li>
                        <li><a href="ui-toasts" key="t-toasts"><?= lang('Files.Toasts') ?></a></li>
                        <li><a href="ui-video" key="t-video"><?= lang('Files.Video') ?></a></li>
                        <li><a href="ui-general" key="t-general"><?= lang('Files.General') ?></a></li>
                        <li><a href="ui-colors" key="t-colors"><?= lang('Files.Colors') ?></a></li>
                        <li><a href="ui-rating" key="t-rating"><?= lang('Files.Rating') ?></a></li>
                        <li><a href="ui-notifications" key="t-notifications"><?= lang('Files.Notifications') ?></a></li>
                        <li><a href="ui-utilities" key="t-utilities"><?= lang('Files.Utilities') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="waves-effect">
                        <i class="bx bxs-eraser"></i>
                        <span class="badge rounded-pill bg-danger float-end">10</span>
                        <span key="t-forms"><?= lang('Files.Forms') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="form-elements" key="t-form-elements"><?= lang('Files.Form Elements') ?></a></li>
                        <li><a href="form-layouts" key="t-form-layouts"><?= lang('Files.Form Layouts') ?></a></li>
                        <li><a href="form-validation" key="t-form-validation"><?= lang('Files.Form Validation') ?></a></li>
                        <li><a href="form-advanced" key="t-form-advanced"><?= lang('Files.Form Advanced') ?></a></li>
                        <li><a href="form-editors" key="t-form-editors"><?= lang('Files.Form Editors') ?></a></li>
                        <li><a href="form-uploads" key="t-form-upload"><?= lang('Files.Form File Upload') ?></a></li>
                        <li><a href="form-xeditable" key="t-form-xeditable"><?= lang('Files.Form Xeditable') ?></a></li>
                        <li><a href="form-repeater" key="t-form-repeater"><?= lang('Files.Form Repeater') ?></a></li>
                        <li><a href="form-wizard" key="t-form-wizard"><?= lang('Files.Form Wizard') ?></a></li>
                        <li><a href="form-mask" key="t-form-mask"><?= lang('Files.Form Mask') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-list-ul"></i>
                        <span key="t-tables"><?= lang('Files.Tables') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="tables-basic" key="t-basic-tables"><?= lang('Files.Basic Tables') ?></a></li>
                        <li><a href="tables-datatable" key="t-data-tables"><?= lang('Files.Data Tables') ?></a></li>
                        <li><a href="tables-responsive" key="t-responsive-table"><?= lang('Files.Responsive Table') ?></a></li>
                        <li><a href="tables-editable" key="t-editable-table"><?= lang('Files.Editable Table') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bxs-bar-chart-alt-2"></i>
                        <span key="t-charts"><?= lang('Files.Charts') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="charts-apex" key="t-apex-charts"><?= lang('Files.Apex Charts') ?></a></li>
                        <li><a href="charts-echart" key="t-e-charts"><?= lang('Files.E Charts') ?></a></li>
                        <li><a href="charts-chartjs" key="t-chartjs-charts"><?= lang('Files.Chartjs Charts') ?></a></li>
                        <li><a href="charts-flot" key="t-flot-charts"><?= lang('Files.Flot Charts') ?></a></li>
                        <li><a href="charts-tui" key="t-ui-charts"><?= lang('Files.Toast UI Charts') ?></a></li>
                        <li><a href="charts-knob" key="t-knob-charts"><?= lang('Files.Jquery Knob Charts') ?></a></li>
                        <li><a href="charts-sparkline" key="t-sparkline-charts"><?= lang('Files.Sparkline Charts') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-aperture"></i>
                        <span key="t-icons"><?= lang('Files.Icons') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="icons-boxicons" key="t-boxicons"><?= lang('Files.Boxicons') ?></a></li>
                        <li><a href="icons-materialdesign" key="t-material-design"><?= lang('Files.Material Design') ?></a></li>
                        <li><a href="icons-dripicons" key="t-dripicons"><?= lang('Files.Dripicons') ?></a></li>
                        <li><a href="icons-fontawesome" key="t-font-awesome"><?= lang('Files.Font Awesome') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-map"></i>
                        <span key="t-maps"><?= lang('Files.Maps') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="maps-google" key="t-g-maps"><?= lang('Files.Google Maps') ?></a></li>
                        <li><a href="maps-vector" key="t-v-maps"><?= lang('Files.Vector Maps') ?></a></li>
                        <li><a href="maps-leaflet" key="t-l-maps"><?= lang('Files.Leaflet Maps') ?></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="bx bx-share-alt"></i>
                        <span key="t-multi-level"><?= lang('Files.Multi Level') ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="javascript: void(0);" key="t-level-1-1"><?= lang('Files.Level') ?> 1.1</a></li>
                        <li>
                            <a href="javascript: void(0);" class="has-arrow" key="t-level-1-2"><?= lang('Files.Level') ?> 1.2</a>
                            <ul class="sub-menu" aria-expanded="true">
                                <li><a href="javascript: void(0);" key="t-level-2-1"><?= lang('Files.Level') ?> 2.1</a></li>
                                <li><a href="javascript: void(0);" key="t-level-2-2"><?= lang('Files.Level') ?> 2.2</a></li>
                            </ul>
                        </li>
                    </ul>
                </li> -->

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->