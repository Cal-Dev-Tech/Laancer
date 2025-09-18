<table>
    <thead>
        <tr>
            <th>{{ __('Payment Gateway') }}</th>
            <th>{{ __('Payment Status') }}</th>
            <th>{{ __('Deposit Amount') }}</th>
            <th>{{ __('Deposit Date') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($all_histories as $history)
            <tr>
                <td>
                    @if ($history->payment_gateway == 'manual_payment' || $history->payment_gateway == 'manual_admin')
                        {{ ucfirst(str_replace('_', ' ', $history->payment_gateway)) }}
                    @else
                        {{ $history->payment_gateway == 'authorize_dot_net' ? __('Authorize.Net') : ucfirst($history->payment_gateway) }}
                    @endif
                </td>
                <td>
                    @php
                        $status = $history->payment_status;
                        $statusClass =
                            $history->type === 'deduction'
                                ? 'btn-danger'
                                : match ($status) {
                                    'complete' => 'btn-success',
                                    'reject', 'rejected' => 'btn-danger',
                                    'cancel', 'canceled' => 'btn-danger',
                                    'pending' => 'btn-warning',
                                    default => 'btn-danger',
                                };
                    @endphp

                    <span class="btn btn-sm {{ $statusClass }}">
                        {{ ucfirst($status ?: 'cancel') }}
                    </span>
                </td>
                <td>
                    @if (
                        $history->type === 'deduction' ||
                            $history->payment_status === 'rejected' ||
                            $history->payment_status === 'cancel' ||
                            $history->payment_status === '')
                        <span class="text-danger">{{ float_amount_with_currency_symbol($history->amount) }}</span>
                    @else
                        <span class="text-success">{{ float_amount_with_currency_symbol($history->amount) }}</span>
                    @endif
                </td>
                <td>{{ $history->created_at }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="deposit-history-pagination mt-4">
    <x-pagination.laravel-paginate :allData="$all_histories" />
</div>
