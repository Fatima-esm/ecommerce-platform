
<x-mail::message>

# Hello {{ $customerName  }},

@if ($status === 'confirmed')

Your order #{{ $orderId }} has been confirmed.

<x-mail::panel>
Order Status: Confirmed
</x-mail::panel>

@elseif ($status === 'cancelled')

Your order #{{ $orderId }} has been cancelled.

If you didn't request this cancellation, please contact us.

<x-mail::panel>
Order Status: Cancelled
</x-mail::panel>

@else

Your order #{{ $orderId }} has been updated.

{{ $status }}

@endif

Thank you for choosing our store.

Thanks, <br>
{{ config('app.name') }}

</x-mail::message>
