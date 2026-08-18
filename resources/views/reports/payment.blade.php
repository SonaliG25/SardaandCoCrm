@extends('layouts.app')

@section('title', 'Payment Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <i class="fas fa-money-bill-wave mr-2" style="color: #f2601f;"></i>
            Payment Report
        </h1>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('reports.payment') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" 
                    class="px-6 py-2 text-white font-medium rounded-lg"
                    style="background: #f2601f;">
                <i class="fas fa-filter mr-2"></i>
                Apply
            </button>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #3b82f6, #2563eb);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Total Receivable</p>
                <p class="text-3xl font-bold">₹{{ number_format($stats['total_receivable']) }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #10b981, #059669);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Total Received</p>
                <p class="text-3xl font-bold">₹{{ number_format($stats['total_received']) }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #ef4444, #dc2626);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Pending</p>
                <p class="text-3xl font-bold">₹{{ number_format($stats['total_pending']) }}</p>
            </div>
        </div>

        <div class="rounded-lg shadow-lg p-6" style="background: linear-gradient(to bottom right, #8b5cf6, #7c3aed);">
            <div style="color: white;">
                <p class="text-sm mb-1" style="opacity: 0.9;">Collection Rate</p>
                <p class="text-3xl font-bold">{{ number_format($stats['collection_rate'], 1) }}%</p>
            </div>
        </div>
    </div>

    <!-- Payment Gateway Breakdown -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment by Gateway</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($gatewayBreakdown as $gateway)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center"
                         style="background: 
                            @if($gateway->payment_gateway == 'razorpay') #f3e8ff
                            @elseif($gateway->payment_gateway == 'cod') #fef3c7
                            @else #f3f4f6
                            @endif">
                        <i class="fas text-xl
                            @if($gateway->payment_gateway == 'razorpay') fa-credit-card
                            @elseif($gateway->payment_gateway == 'cod') fa-money-bill
                            @else fa-question
                            @endif"
                           style="color: 
                            @if($gateway->payment_gateway == 'razorpay') #9333ea
                            @elseif($gateway->payment_gateway == 'cod') #ca8a04
                            @else #6b7280
                            @endif"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ ucfirst($gateway->payment_gateway ?? 'Other') }}</p>
                        <p class="text-sm text-gray-600">{{ $gateway->count }} orders</p>
                    </div>
                </div>
                <p class="text-xl font-bold text-gray-900">₹{{ number_format($gateway->total) }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Payment Status Breakdown -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Status Distribution</h3>
        <div class="space-y-3">
            @foreach($paymentStatusBreakdown as $status)
            @php
                $totalReceivable = $paymentStatusBreakdown->sum('total');
                $percentage = $totalReceivable > 0 ? ($status->total / $totalReceivable) * 100 : 0;
                
                $colors = [
                    'received' => ['bg' => '#10b981', 'light' => '#d1fae5'],
                    'pending' => ['bg' => '#ef4444', 'light' => '#fee2e2'],
                    'partial' => ['bg' => '#f59e0b', 'light' => '#fef3c7'],
                ];
                
                $color = $colors[$status->payment_status] ?? ['bg' => '#6b7280', 'light' => '#f3f4f6'];
            @endphp
            
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                         style="background: {{ $color['light'] }}">
                        <i class="fas 
                            @if($status->payment_status == 'received') fa-check-circle
                            @elseif($status->payment_status == 'pending') fa-clock
                            @else fa-exclamation-circle
                            @endif"
                           style="color: {{ $color['bg'] }}"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ ucfirst($status->payment_status) }}</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="h-2 rounded-full" 
                                 style="width: {{ $percentage }}%; background: {{ $color['bg'] }}"></div>
                        </div>
                    </div>
                </div>
                <div class="text-right ml-4">
                    <p class="font-bold text-gray-900">₹{{ number_format($status->total) }}</p>
                    <p class="text-xs text-gray-500">{{ $status->count }} orders</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Pending Payments</h3>
            <span class="px-3 py-1 rounded-full text-sm font-semibold"
                  style="background: #fee2e2; color: #991b1b;">
                {{ $pendingPayments->count() }} Orders
            </span>
        </div>
        
        @if($pendingPayments->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingPayments->take(20) as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $order->woocommerce_order_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->customer->phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $order->order_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold" style="color: #dc2626;">
                            ₹{{ number_format($order->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('orders.show', $order) }}" 
                               style="color: #f2601f;"
                               class="hover:underline">
                                View →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($pendingPayments->count() > 20)
        <p class="text-sm text-gray-500 mt-4 text-center">Showing first 20 of {{ $pendingPayments->count() }} pending payments</p>
        @endif
        @else
        <div class="text-center py-12">
            <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
            <p class="text-gray-600 font-medium">All payments received! 🎉</p>
            <p class="text-sm text-gray-500 mt-1">No pending payments for this period</p>
        </div>
        @endif
    </div>
</div>
@endsection