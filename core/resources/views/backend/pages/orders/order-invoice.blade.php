<!DOCTYPE html>
<html lang="{{ get_user_lang() }}" dir="{{ get_user_lang_direction() }}">

<head>
    <title>{{ __('Order Invoice') }}</title>
    @php
        $site_favicon = get_attachment_image_by_id(get_static_option('site_favicon'), 'full', false);
        $is_rtl = get_user_lang_direction() === 'rtl';
        $alignEnd = $is_rtl ? 'text-right' : 'text-left';
    @endphp
    @if ($site_favicon)
        <link rel="icon" href="{{ $site_favicon['img_url'] ?? '' }}" sizes="40x40" type="icon/png">
    @endif
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css" media="screen">
        html {
            font-family: sans-serif;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            font-size: 10px;
            margin: 36pt;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
        }

        th {
            text-align: inherit;
        }

        h4,
        .h4 {
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
            font-size: 1.5rem;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .table.table-items td {
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .mt-5 {
            margin-top: 3rem !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-left {
            text-align: left !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        * {
            font-family: "DejaVu Sans";
        }

        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        .party-header {
            font-size: 1.5rem;
            font-weight: 400;
        }

        .total-amount {
            font-size: 12px;
            font-weight: 700;
        }

        .border-0 {
            border: none !important;
        }

        .cool-gray {
            color: #6B7280;
        }

        .invoice-logo img {
            width: 200px;
            height: 40px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    @if (get_static_option('site_logo'))
        <div class="invoice-logo">
            @php
                $logoId = get_static_option('site_logo');
                $imageData = get_attachment_image_by_id($logoId, null, false);
                $logoUrl = $imageData['img_url'] ?? null;
                function fetchImageContents($url)
                {
                    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                        return false;
                    }
                    if (ini_get('allow_url_fopen')) {
                        $image = @file_get_contents($url);
                        if ($image !== false) {
                            return $image;
                        }
                    }
                    if (function_exists('curl_version')) {
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        // SSL bypass - **WARNING: only do this if you trust the URL!**
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                        $image = curl_exec($ch);
                        if (curl_errno($ch)) {
                            echo '<p>cURL error: ' . curl_error($ch) . '</p>';
                        }
                        curl_close($ch);
                        if ($image !== false) {
                            return $image;
                        }
                    }
                    return false;
                }
                $base64Image = '';
                if ($logoUrl) {
                    $imageContents = fetchImageContents($logoUrl);
                    if ($imageContents !== false) {
                        $ext = strtolower(pathinfo(parse_url($logoUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                        $mimeTypes = [
                            'png' => 'image/png',
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'gif' => 'image/gif',
                            'bmp' => 'image/bmp',
                            'svg' => 'image/svg+xml',
                        ];
                        $mimeType = $mimeTypes[$ext] ?? 'image/png';
                        $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                    } else {
                        echo '<p>Failed to fetch image contents from URL.</p>';
                    }
                } else {
                    echo '<p>No valid logo URL found.</p>';
                }
            @endphp
            @if ($base64Image)
                <img src="{{ $base64Image }}" alt="Site Logo" style="max-width: 200px;">
            @else
                {!! render_image_markup_by_attachment_id(get_static_option('site_logo')) !!}
            @endif
        </div>
    @endif

    <table class="table mt-5">
        <tbody>
            <tr>
                @if ($is_rtl)
                    <td class="border-0 pr-0">
                        <p>{{ __('Order ID') }} <strong>#000{{ $order->id }}</strong></p>
                        <p>{{ __('Invoice ID') }} <strong>#{{ $order->invoice_no }}</strong></p>
                        <p>{{ __('Invoice Date') }}: <strong>{{ \Carbon\Carbon::now()->toDateString() }}</strong></p>
                    </td>
                    <td class="border-0 pl-0" width="70%">
                        <h4 class="text-uppercase text-right">
                            <strong>{{ __('Order Invoice') }}</strong>
                        </h4>
                    </td>
                @else
                    <td class="border-0 pl-0" width="70%">
                        <h4 class="text-uppercase">
                            <strong>{{ __('Order Invoice') }}</strong>
                        </h4>
                    </td>
                    <td class="border-0 px-0">
                        <p>{{ __('Order ID') }} <strong>#000{{ $order->id }}</strong></p>
                        <p>{{ __('Invoice ID') }} <strong>#{{ $order->invoice_no }}</strong></p>
                        <p>{{ __('Invoice Date') }}: <strong>{{ \Carbon\Carbon::now()->toDateString() }}</strong></p>
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    {{-- Seller - Buyer --}}
    <table class="table">
        <thead>
            <tr>
                @if ($is_rtl)
                    <th class="border-0 pr-0 party-header text-right">
                        {{ __('Client') }}
                    </th>
                    <th class="border-0" width="3%"></th>
                    <th class="border-0 pl-0 party-header text-right" width="48.5%">
                        {{ __('Freelancer') }}
                    </th>
                @else
                    <th class="border-0 pl-0 party-header text-left" width="48.5%">
                        {{ __('Freelancer') }}
                    </th>
                    <th class="border-0" width="3%"></th>
                    <th class="border-0 px-0 party-header text-left">
                        {{ __('Client') }}
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                @if ($is_rtl)
                    <td class="px-0 text-right">
                        @if ($order?->user->fullname)
                            <p class="buyer-name">
                                <strong>{{ $order?->user->fullname }}</strong>
                            </p>
                        @endif

                        @if ($order?->user->email)
                            <p class="seller-address">
                                {{ __('Email') }}: {{ $order?->user->email }}
                            </p>
                        @endif

                        @if ($order?->user->phone)
                            <p class="buyer-phone">
                                {{ __('Phone') }}: {{ $order->user->phone }}
                            </p>
                        @endif
                    </td>
                    <td class="border-0"></td>
                    <td class="px-0 text-right">
                        @if ($order?->freelancer->fullname)
                            <p class="seller-name">
                                <strong>{{ $order?->freelancer->fullname }}</strong>
                            </p>
                        @endif

                        @if ($order?->freelancer->email)
                            <p class="seller-address">
                                {{ __('Email') }}: {{ $order?->freelancer->email }}
                            </p>
                        @endif

                        @if ($order?->freelancer->phone)
                            <p class="seller-phone">
                                {{ __('Phone') }}: {{ $order->freelancer->phone }}
                            </p>
                        @endif
                    </td>
                @else
                    <td class="pl-0">
                        @if ($order?->freelancer->fullname)
                            <p class="seller-name">
                                <strong>{{ $order?->freelancer->fullname }}</strong>
                            </p>
                        @endif

                        @if ($order?->freelancer->email)
                            <p class="seller-address">
                                {{ __('Email') }}: {{ $order?->freelancer->email }}
                            </p>
                        @endif

                        @if ($order?->freelancer->phone)
                            <p class="seller-phone">
                                {{ __('Phone') }}: {{ $order->freelancer->phone }}
                            </p>
                        @endif
                    </td>
                    <td class="border-0"></td>
                    <td class="pl-0">
                        @if ($order?->user->fullname)
                            <p class="buyer-name">
                                <strong>{{ $order?->user->fullname }}</strong>
                            </p>
                        @endif

                        @if ($order?->user->email)
                            <p class="seller-address">
                                {{ __('Email') }}: {{ $order?->user->email }}
                            </p>
                        @endif

                        @if ($order?->user->phone)
                            <p class="buyer-phone">
                                {{ __('Phone') }}: {{ $order->user->phone }}
                            </p>
                        @endif
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    {{-- Table --}}
    <table class="table table-items">
        <thead>
            <tr>
                @if ($is_rtl)
                    <th scope="col" class="border-0 pr-0 amount-cell">{{ __('Sub total') }}</th>
                    <th scope="col" class="border-0 pr-0 text-right">{{ __('Price') }}</th>
                    <th scope="col" class="border-0 pr-0 text-right">{{ __('Quantity') }}</th>
                    <th scope="col" class="border-0 pl-0 text-right">{{ __('Description') }}</th>
                @else
                    <th scope="col" class="border-0 pl-0">{{ __('Description') }}</th>
                    <th scope="col" class="border-0 pl-0">{{ __('Quantity') }}</th>
                    <th scope="col" class="border-0 pl-0">{{ __('Price') }}</th>
                    <th scope="col" class="border-0 px-0 text-right amount-cell">{{ __('Sub total') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {{-- Items --}}
            <tr>
                @if ($is_rtl)
                    <td class="pr-0 amount-cell">
                        {{ float_amount_with_currency_symbol($order->price - ($order->transaction_amount + $order->commission_amount)) }}
                    </td>
                    <td class="pr-0 text-right">
                        {{ float_amount_with_currency_symbol($order->price - ($order->transaction_amount + $order->commission_amount)) }}
                    </td>
                    <td class="pr-0 text-right">1</td>
                    <td class="pl-0 text-right">
                        <p class="cool-gray">
                            {{ __('Order Date:') }} {{ $order->created_at->toFormattedDateString() }} <br>
                            {{ __('Payment Gateway:') }}
                            @if ($order->payment_gateway == 'manual_payment')
                                {{ ucfirst(str_replace('_', ' ', $order->payment_gateway)) }}
                            @else
                                {{ $order->payment_gateway == 'authorize_dot_net' ? __('Authorize.Net') : ucfirst($order->payment_gateway) }}
                            @endif
                        </p>
                    </td>
                @else
                    <td class="pl-0">
                        <p class="cool-gray">
                            {{ __('Order Date:') }} {{ $order->created_at->toFormattedDateString() }} <br>
                            {{ __('Payment Gateway:') }}
                            @if ($order->payment_gateway == 'manual_payment')
                                {{ ucfirst(str_replace('_', ' ', $order->payment_gateway)) }}
                            @else
                                {{ $order->payment_gateway == 'authorize_dot_net' ? __('Authorize.Net') : ucfirst($order->payment_gateway) }}
                            @endif
                        </p>
                    </td>
                    <td class="pl-0">1</td>
                    <td class="pl-0">
                        {{ float_amount_with_currency_symbol($order->price - ($order->transaction_amount + $order->commission_amount)) }}
                    </td>
                    <td class="pr-0 text-right amount-cell">
                        {{ float_amount_with_currency_symbol($order->price - ($order->transaction_amount + $order->commission_amount)) }}
                    </td>
                @endif
            </tr>
            {{-- Summary --}}
            <tr>
                @if ($is_rtl)
                    <td class="pr-0 amount-cell">{{ float_amount_with_currency_symbol($order->transaction_amount) }}
                    </td>
                    <td class="pl-0 text-right">{{ __('Transaction fee') }}</td>
                    <td colspan="2" class="border-0 text-right"></td>
                @else
                    <td colspan="2" class="border-0"></td>
                    <td class="pl-0">{{ __('Transaction fee') }}</td>
                    <td class="pr-0 text-right amount-cell">
                        {{ float_amount_with_currency_symbol($order->transaction_amount) }}
                    </td>
                @endif
            </tr>
            <tr>
                @if ($is_rtl)
                    <td class="pr-0 amount-cell">{{ float_amount_with_currency_symbol($order->commission_amount) }}
                    </td>
                    <td class="pl-0 text-right">{{ __('Commission amount') }}</td>
                    <td colspan="2" class="border-0 text-right"></td>
                @else
                    <td colspan="2" class="border-0"></td>
                    <td class="pl-0">{{ __('Commission amount') }}</td>
                    <td class="pr-0 text-right amount-cell">
                        {{ float_amount_with_currency_symbol($order->commission_amount) }}
                    </td>
                @endif
            </tr>
            <tr>
                @if ($is_rtl)
                    <td class="pr-0 amount-cell total-amount">{{ float_amount_with_currency_symbol($order->price) }}
                    </td>
                    <td class="pl-0 text-right">{{ __('Total amount') }}</td>
                    <td colspan="2" class="border-0 text-right"></td>
                @else
                    <td colspan="2" class="border-0"></td>
                    <td class="pl-0">{{ __('Total amount') }}</td>
                    <td class="pr-0 text-right amount-cell total-amount">
                        {{ float_amount_with_currency_symbol($order->price) }}
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    <br>
    <p class="{{ $alignEnd }}">
        {{ __('Amount in words') }}: {{ \Terbilang::make($order->price) }}
    </p>

    @if ($order->description)
        <p class="{{ $alignEnd }}">
            {{ __('Notes') }}: {{ __(Str::limit($order->description, 300)) ?? '' }}
        </p>
    @endif
</body>

</html>
