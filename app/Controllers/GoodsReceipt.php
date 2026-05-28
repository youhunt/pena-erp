<?php

namespace App\Controllers;

use App\Models\GoodsReceiptReadModel;
use App\Models\GoodsReceiptWriteModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class GoodsReceipt extends BaseController
{
    protected GoodsReceiptReadModel $readModel;
    protected GoodsReceiptWriteModel $writeModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->readModel = new GoodsReceiptReadModel();
        $this->writeModel = new GoodsReceiptWriteModel();
    }

    public function index()
    {
        $companyId = (int) session('tenant.company_id');

        return view('purchasing/receipts', [
            'receipts' => $this->readModel->listReceipts($companyId),
            'purchaseOrders' => $this->readModel->listPurchaseOrders($companyId),
        ]);
    }

    public function create()
    {
        if (! $this->request->is('post')) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        try {
            $result = $this->writeModel->createDraftReceipt([
                'purchase_order_id' => (int) $this->request->getPost('purchase_order_id'),
                'purchase_order_item_id' => (int) $this->request->getPost('purchase_order_item_id'),
                'warehouse_id' => (int) $this->request->getPost('warehouse_id'),
                'qty_received' => (float) $this->request->getPost('qty_received'),
            ]);

            return redirect()->to('purchasing/receipts')
                ->with('success', 'Goods receipt draft created: #' . $result['id']);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function post($id)
    {
        try {
            $result = $this->writeModel->postReceipt((int) $id);

            return redirect()->to('purchasing/receipts')
                ->with('success', 'Goods receipt posted: #' . $result['id']);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
