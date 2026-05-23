<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    /**
     * Initiate a Paystack payment for a given order.
     */
    public function initiate(Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === auth()->id(), 403);
        abort_unless($order->payment_status === 'unpaid', 422, 'This order has already been paid.');

        $user = auth()->user();

        $response = $this->paystackPost('/transaction/initialize', [
            'email'        => $user->email,
            'amount'       => (int) ($order->total_amount * 100), // Paystack uses kobo
            'reference'    => 'LASU-PAY-' . $order->id . '-' . time(),
            'callback_url' => route('payment.callback'),
            'metadata'     => [
                'order_id'   => $order->id,
                'order_number'=> $order->order_number,
                'buyer_name' => $user->name,
            ],
        ]);

        if (!$response || !$response['status']) {
            return back()->with('error', 'Could not initiate payment. Please try again.');
        }

        // Update payment record with reference
        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            ['provider_reference' => $response['data']['reference'], 'status' => 'pending']
        );

        return redirect($response['data']['authorization_url']);
    }

    /**
     * Paystack redirects user here after payment.
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('buyer.orders.index')->with('error', 'Invalid payment reference.');
        }

        $response = $this->paystackGet('/transaction/verify/' . $reference);

        if (!$response || $response['data']['status'] !== 'success') {
            return redirect()->route('buyer.orders.index')->with('error', 'Payment verification failed.');
        }

        $this->fulfillPayment($response['data']);

        return redirect()->route('buyer.orders.index')->with('success', 'Payment successful! Your order is confirmed.');
    }

    /**
     * Paystack webhook for server-side confirmation.
     */
    public function webhook(Request $request): Response
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $hash      = hash_hmac('sha512', $request->getContent(), $this->secretKey);

        if (!hash_equals($hash, $signature ?? '')) {
            Log::warning('Invalid Paystack webhook signature');
            return response('Unauthorized', 401);
        }

        $payload = $request->json()->all();

        if (($payload['event'] ?? '') === 'charge.success') {
            $this->fulfillPayment($payload['data']);
        }

        return response('OK', 200);
    }

    /**
     * Shared logic to mark order paid after successful Paystack response.
     */
    private function fulfillPayment(array $data): void
    {
        $orderId = $data['metadata']['order_id'] ?? null;
        if (!$orderId) return;

        $order = Order::find($orderId);
        if (!$order || $order->payment_status === 'paid') return;

        $payment = $order->payment;
        if (!$payment) return;

        $payment->update([
            'status'          => 'success',
            'provider_reference' => $data['reference'],
            'amount'          => $data['amount'] / 100,
            'gateway_payload' => $data,
            'paid_at'         => now(),
        ]);

        $order->update([
            'payment_status' => 'paid',
            'order_status'   => 'confirmed',
            'paid_at'        => now(),
            'confirmed_at'   => now(),
        ]);

        // Notify buyer and seller
        $order->buyer->notify(new PaymentConfirmedNotification($order));
        $order->seller->notify(new PaymentConfirmedNotification($order));
    }

    /**
     * POST request to Paystack API.
     */
    private function paystackPost(string $endpoint, array $data): ?array
    {
        try {
            $ch = curl_init($this->baseUrl . $endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($data),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->secretKey,
                    'Content-Type: application/json',
                    'Cache-Control: no-cache',
                ],
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        } catch (\Throwable $e) {
            Log::error('Paystack POST error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * GET request to Paystack API.
     */
    private function paystackGet(string $endpoint): ?array
    {
        try {
            $ch = curl_init($this->baseUrl . $endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $this->secretKey,
                    'Cache-Control: no-cache',
                ],
            ]);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        } catch (\Throwable $e) {
            Log::error('Paystack GET error: ' . $e->getMessage());
            return null;
        }
    }
}
