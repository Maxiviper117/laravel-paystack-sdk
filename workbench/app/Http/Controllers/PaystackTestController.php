<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maxiviper117\Paystack\Actions\Customer\ListCustomersAction;
use Maxiviper117\Paystack\Actions\Transaction\InitializeTransactionAction;
use Maxiviper117\Paystack\Actions\Transaction\VerifyTransactionAction;
use Maxiviper117\Paystack\Data\Input\Customer\ListCustomersInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\InitializeTransactionInputData;
use Maxiviper117\Paystack\Data\Input\Transaction\VerifyTransactionInputData;
use Maxiviper117\Paystack\Data\Output\Customer\ListCustomersResponseData;

class PaystackTestController extends Controller
{
    public function start(InitializeTransactionAction $initializeTransaction): RedirectResponse
    {
        $initialized = $initializeTransaction(
            new InitializeTransactionInputData(
                email: 'customer@example.com',
                amount: 15.50,
                callbackUrl: url('/paystack/test/callback'),
                metadata: [
                    'source' => 'workbench',
                    'purpose' => 'live-test',
                ],
            ),
        );

        return redirect()->away($initialized->authorizationUrl);
    }

    public function callback(Request $request, VerifyTransactionAction $verifyTransaction): JsonResponse
    {
        $reference = (string) $request->query('reference', '');

        abort_if($reference === '', 400, 'Missing Paystack reference.');

        $verified = $verifyTransaction(new VerifyTransactionInputData($reference));

        return response()->json([
            'message' => 'Payment verified successfully.',
            'transaction' => [
                'reference' => $verified->transaction->reference,
                'status' => $verified->transaction->status,
                'amount' => $verified->transaction->amount,
                'currency' => $verified->transaction->currency,
                'paid_at' => $verified->transaction->paidAt?->toAtomString(),
            ],
        ]);
    }

    public function customers(Request $request, ListCustomersAction $listCustomers): ListCustomersResponseData
    {
        return $listCustomers(new ListCustomersInputData(
            perPage: $request->integer('per_page') ?: null,
            page: $request->integer('page') ?: null,
            email: $request->filled('email') ? (string) $request->query('email') : null,
            from: $request->filled('from') ? (string) $request->query('from') : null,
            to: $request->filled('to') ? (string) $request->query('to') : null,
        ));
    }
}
