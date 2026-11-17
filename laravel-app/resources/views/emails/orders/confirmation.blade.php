<x-mail::message>
# Hello {{ $customerName }},

Your order **#{{ $orderId }}** has been received and is being processed.

- **Status:** {{ $status }}
- **Total:** ${{ $total }}

We will keep you updated. Thanks for shopping with us!

Thanks, <br>
{{ config('app.name') }}
</x-mail::message>