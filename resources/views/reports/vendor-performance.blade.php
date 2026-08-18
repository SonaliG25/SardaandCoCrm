@extends('layouts.app')

@section('title', 'Vendor Performance Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-users-cog text-sarda-600 mr-2"></i>
            Vendor Performance Report
        </h1>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('reports.vendor-performance') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" 
                       name="start_date" 
                       value="{{ $startDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sarda-500 focus:border-sarda-500">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" 
                       name="end_date" 
                       value="{{ $endDate }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sarda-500 focus:border-sarda-500">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-sarda-600 hover:bg-sarda-700 text-white font-medium rounded-lg">
                <i class="fas fa-filter mr-2"></i>
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Vendors Performance Cards -->
    <div class="space-y-6">
        @foreach($vendors as $data)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Vendor Header -->
            <div class="bg-gradient-to-r from-sarda-500 to-sarda-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                            <span class="text-xl font-bold text-sarda-600">
                                {{ substr($data['vendor']->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ $data['vendor']->name }}</h3>
                            <p class="text-sm text-sarda-100">{{ $data['vendor']->contact_person }} • {{ $data['vendor']->phone }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-sarda-100">Total Orders</p>
                        <p class="text-3xl font-bold text-white">{{ $data['total_orders'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <!-- Completion Rate -->
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-4 flex items-center justify-center
                            @if($data['completion_rate'] >= 90) border-green-500
                            @elseif($data['completion_rate'] >= 70) border-yellow-500
                            @else border-red-500
                            @endif">
                            <span class="text-xl font-bold
                                @if($data['completion_rate'] >= 90) text-green-600
                                @elseif($data['completion_rate'] >= 70) text-yellow-600
                                @else text-red-600
                                @endif">
                                {{ number_format($data['completion_rate'], 0) }}%
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Completion Rate</p>
                        <p class="text-xs text-gray-500">{{ $data['completed_orders'] }}/{{ $data['total_orders'] }} orders</p>
                    </div>

                    <!-- On-Time Delivery -->
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-4 flex items-center justify-center
                            @if($data['on_time_delivery'] >= 90) border-green-500
                            @elseif($data['on_time_delivery'] >= 70) border-yellow-500
                            @else border-red-500
                            @endif">
                            <span class="text-xl font-bold
                                @if($data['on_time_delivery'] >= 90) text-green-600
                                @elseif($data['on_time_delivery'] >= 70) text-yellow-600
                                @else text-red-600
                                @endif">
                                {{ number_format($data['on_time_delivery'], 0) }}%
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">On-Time Delivery</p>
                        <p class="text-xs text-gray-500">Based on completed orders</p>
                    </div>

                    <!-- Dye Performance -->
                    @if($data['dye']['total'] > 0)
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-4 border-blue-500 flex items-center justify-center">
                            <span class="text-xl font-bold text-blue-600">
                                {{ number_format(($data['dye']['completed'] / $data['dye']['total']) * 100, 0) }}%
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Dye</p>
                        <p class="text-xs text-gray-500">{{ $data['dye']['completed'] }}/{{ $data['dye']['total'] }} • {{ $data['dye']['on_time'] }} on-time</p>
                    </div>
                    @endif

                    <!-- Print Performance -->
                    @if($data['print']['total'] > 0)
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-4 border-purple-500 flex items-center justify-center">
                            <span class="text-xl font-bold text-purple-600">
                                {{ number_format(($data['print']['completed'] / $data['print']['total']) * 100, 0) }}%
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Print</p>
                        <p class="text-xs text-gray-500">{{ $data['print']['completed'] }}/{{ $data['print']['total'] }} • {{ $data['print']['on_time'] }} on-time</p>
                    </div>
                    @endif

                    <!-- Embroidery Performance -->
                    @if($data['emb']['total'] > 0)
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-2 rounded-full border-4 border-pink-500 flex items-center justify-center">
                            <span class="text-xl font-bold text-pink-600">
                                {{ number_format(($data['emb']['completed'] / $data['emb']['total']) * 100, 0) }}%
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Embroidery</p>
                        <p class="text-xs text-gray-500">{{ $data['emb']['completed'] }}/{{ $data['emb']['total'] }} • {{ $data['emb']['on_time'] }} on-time</p>
                    </div>
                    @endif
                </div>

                <!-- Performance Rating -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Overall Performance Rating</p>
                            <p class="text-xs text-gray-500 mt-1">Based on completion and on-time delivery</p>
                        </div>
                        <div class="flex items-center space-x-1">
                            @php
                                $rating = ($data['completion_rate'] + $data['on_time_delivery']) / 2;
                                $stars = round($rating / 20);
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $stars ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="ml-2 text-sm font-medium text-gray-900">{{ number_format($rating, 1) }}/100</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($vendors->count() == 0)
        <div class="bg-white rounded-lg shadow-sm p-12 text-center">
            <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-600">No vendor performance data available for the selected period.</p>
        </div>
        @endif
    </div>
</div>
@endsection