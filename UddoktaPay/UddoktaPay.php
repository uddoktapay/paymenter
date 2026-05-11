<?php

namespace Paymenter\Extensions\Gateways\UddoktaPay;

use App\Classes\Extension\Gateway;
use App\Exceptions\DisplayException;
use App\Helpers\ExtensionHelper;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UddoktaPay extends Gateway
{
    public function boot()
    {
        require __DIR__ . '/routes.php';
    }

    private function request($url, $data = [])
    {
        $response = Http::withHeaders([
            'X-API-KEY' => $this->config('api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(rtrim($this->config('base_url'), '/') . $url, $data);

        if (!$response->successful()) {
            $message = $response->json('message') ?? 'Unknown error occurred';
            throw new DisplayException('UddoktaPay API error: ' . $message);
        }

        return $response->json();
    }

    public function getConfig($values = [])
    {
        return [
            [
                'name' => 'api_key',
                'label' => 'API KEY',
                'type' => 'text',
                'description' => 'You can find your API KEY under System Settings > API Settings.',
                'required' => true,
            ],
            [
                'name' => 'base_url',
                'label' => 'BASE URL',
                'type' => 'text',
                'description' => 'You can find your BASE URL under System Settings > API Settings.',
                'required' => true,
            ],
        ];
    }

    public function pay(Invoice $invoice, $total)
    {
        $product = $this->getProduct($invoice);

        $response = $this->request('/checkout-v2', [
            'amount' => number_format($total, 2, '.', ''),
            'currency' => $invoice->currency_code,
            'full_name' => $invoice->user->name,
            'email' => $invoice->user->email,
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
            ],
            'redirect_url' => route('extensions.gateways.uddoktapay.success', ['invoice' => $invoice->id]),
            'cancel_url' => route('invoices.show', $invoice),
            'webhook_url' => route('extensions.gateways.uddoktapay.webhook', ['invoice' => $invoice->id]),
        ]);

        return $response['payment_url'];
    }

    private function getProduct(Invoice $invoice): ?Product
    {
        $product = null;
        foreach ($invoice->items as $item) {
            if ($item->reference_type !== Service::class) {
                continue;
            }
            $product = $item->reference->product;
            break;
        }
        return $product;
    }

    public function success(Request $request, Invoice $invoice)
    {
        $payment = $this->request('/verify-payment', [
            'invoice_id' => $request->input('invoice_id'),
        ]);

        if (strtolower($payment['status'] ?? '') === 'completed') {
            ExtensionHelper::addPayment(
                $payment['metadata']['invoice_id'],
                'UddoktaPay',
                $payment['amount'],
                $payment['fee'] ?? 0,
                $payment['transaction_id']
            );
        }

        return redirect(route('invoices.show', $invoice) . '?checkPayment=true');
    }

    public function webhook(Request $request)
    {
        $payment = $this->request('/verify-payment', [
            'invoice_id' => $request->input('invoice_id'),
        ]);

        if (strtolower($payment['status'] ?? '') === 'completed') {
            ExtensionHelper::addPayment(
                $payment['metadata']['invoice_id'],
                'UddoktaPay',
                $payment['amount'],
                $payment['fee'] ?? 0,
                $payment['transaction_id']
            );
        }

        return response()->json(['status' => 'ok']);
    }
}
