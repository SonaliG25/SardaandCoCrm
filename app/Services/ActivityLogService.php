<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
     public static function log($action, $module, $recordId, $recordType, $description, $oldValues = null, $newValues = null)
    {
        $user = Auth::user();
        
        if (!$user) return;

        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role?->name ?? 'Unknown',
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'record_type' => $recordType,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            // created_at handled automatically by Laravel
        ]);
    }
    
    // Log order creation
public static function logOrderCreated($order)
{
    self::log(
        'created',
        'orders',
        $order->id,
        'Order',
        "Created order #{$order->order_id}",
        null,
        $order->toArray()
    );
}

// Log order edit
public static function logOrderEdited($order, $oldData)
{
    $changes = [];
    foreach ($oldData as $key => $oldValue) {
        if ($order->$key != $oldValue) {
            $changes[$key] = [
                'old' => $oldValue,
                'new' => $order->$key
            ];
        }
    }

    self::log(
        'edited',
        'orders',
        $order->id,
        'Order',
        "Updated order #{$order->order_id}",
        $oldData,
        $order->toArray()
    );
}

// Log order deleted
public static function logOrderDeleted($order)
{
    self::log(
        'deleted',
        'orders',
        $order->id,
        'Order',
        "Deleted order #{$order->order_id}",
        $order->toArray(),
        null
    );
}
}