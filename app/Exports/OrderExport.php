<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $order = DB::table('orders')
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->join('price_lists', 'price_lists.id', '=', 'orders.price_list_id')
            ->select([
                'orders.*', 'products.name as product', 'price_lists.name as item'
            ])
            ->where('orders.method', '<>', 'top up');

        if ($this->request->product_id && $this->request->product_id !== null) {
            $order->where('orders.product_id',  $this->request->product_id);
        }

        if ($this->request->status && $this->request->status !== null) {
            $order->where('orders.status',  $this->request->status);
        }

        if ($this->request->status_delivery && $this->request->status_delivery !== null) {
            $order->where('orders.status_delivery',  $this->request->status_delivery);
        }

        if ($this->request->type && $this->request->type !== null) {
            $order->where('orders.type',  $this->request->type);
        }

        if ($this->request->end_date && $this->request->end_date !== null && $this->request->start_date && $this->request->start_date !== null) {
            $order->whereBetween('orders.created_at',  [date('Y-m-d', strtotime($this->request->start_date)), date('Y-m-d', strtotime($this->request->end_date))]);
        }

        $i = 0;

        $data = [];

        $order = $order->get();

        foreach ($order as $row) {
            $i++;

            $nett = (($row->total - $row->admin) - $row->admin);

            $entry = [
                'A' => (string)$i,
                'B' => tanggalIndoFull($row->created_at),
                'C' => $row->phone,
                'D' => $row->reference,
                'E' => $row->product,
                'F' => $row->item,
                'G' => $row->status,
                'H' => $row->status_delivery,
                'I' => 'Rp. ' . number_format($row->amount),
                'J' => 'Rp. ' . number_format($row->admin),
                'K' => 'Rp. ' . number_format($row->discount),
                'L' => 'Rp. ' . number_format($row->total),
                'M' => 'Rp. ' . number_format($nett),
            ];

            array_push($data, $entry);
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No', 'Tanggal Transaksi', 'No. Whatsapp',
            'No. Transaksi', 'Produk', 'Item',
            'Status Pembayaran', 'Status Pesanan', 'Harga Pesanan',
            'Biaya Admin', 'Discount', 'Total',
            'Untung bersih'
        ];
    }
}
