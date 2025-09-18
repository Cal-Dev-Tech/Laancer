<style>
    .alert-success {
        border-color: #f2f2f2;
        border-left: 5px solid #319a31;
        background-color: #f2f2f2;
        color: #333;
        border-radius: 0;
        padding: 5px;
    }

    .alert-danger {
        border-color: #f2f2f2;
        border-left: 5px solid #c69500;
        background-color: #f2f2f2;
        color: #333;
        border-radius: 0;
        padding: 5px;
    }

    .alert-cancel {
        border-color: #f2f2f2;
        border-left: 5px solid #f44336;
        background-color: #f2f2f2;
        color: #333;
        border-radius: 0;
        padding: 5px;
    }

    .alert-pending {
        border-color: #f2f2f2;
        border-left: 5px solid #ff9800;
        background-color: #f2f2f2;
        color: #333;
        border-radius: 0;
        padding: 5px;
    }
</style>

@if ($status === 'paid')
    <span class="alert alert-success">{{ __('Paid') }}</span>
@elseif($status === 'pending')
    <span class="alert alert-pending">{{ __('Pending') }}</span>
@elseif($status === 'in_transit')
    <span class="alert alert-danger">{{ __('In Transit') }}</span>
@elseif($status === 'canceled')
    <span class="alert alert-cancel">{{ __('Canceled') }}</span>
@elseif($status === 'failed')
    <span class="alert alert-cancel">{{ __('Failed') }}</span>
@else
    <span class="alert alert-danger">{{ ucfirst($status) }}</span>
@endif
