<table>
    <thead>
        <tr>
            <th>{{ __('Amount') }}</th>
            <th>{{ __('Account Info') }}</th>
            <th>{{ __('Freelancer Info') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($all_request as $request)
            <tr>
                <td>{{ number_format($request->amount, 2) }} {{ strtoupper($request->currency) }}</td>
                <td>
                    <p>{{ __('Account Name:') }} {{ $request?->user?->stripeConnect->getDisplayName() }}</p>
                    <p>{{ __('Stripe Payout ID:') }} {{ $request->stripe_payout_id }}</p>
                </td>
                <td>
                    <p>{{ __('Name:') }} {{ $request?->user->fullname }}</p>
                    <p>{{ __('Email:') }} {{ $request?->user->email }}</p>
                </td>
                <td>
                    <x-status.table.stripe-payout-status :status="$request->status" />
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="deposit-history-pagination mt-4">
    <x-pagination.laravel-paginate :allData="$all_request" />
</div>
