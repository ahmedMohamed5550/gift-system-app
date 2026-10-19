<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    /**
     * Map of system action keys to human-readable Arabic descriptions.
     */
    public const ACTION_LABELS = [
        'user_created' => 'إنشاء مستخدم جديد',
        'user_updated' => 'تعديل بيانات مستخدم',
        'user_status_toggled' => 'تغيير حالة مستخدم (تفعيل/تعطيل)',
        'user_deleted' => 'حذف مستخدم',

        'product_created' => 'إضافة منتج جديد للمخزون',
        'product_updated' => 'تعديل بيانات وأسعار منتج',
        'product_status_toggled' => 'تغيير حالة منتج (تفعيل/تعطيل)',
        'product_deleted' => 'حذف منتج',

        'stock_adjusted' => 'تسوية رصيد مخزني يدوي',

        'sale_created' => 'إنشاء فاتورة مبيعات',
        'sale_cancelled' => 'إلغاء فاتورة مبيعات واسترجاع المخزون',

        'return_created' => 'إنشاء إشعار مرتجع مبيعات',
        'return_cancelled' => 'إلغاء إشعار مرتجع مبيعات',

        'expense_created' => 'تسجيل مصروف جديد',
        'expense_updated' => 'تعديل بيانات مصروف',
        'expense_cancelled' => 'إلغاء مصروف تشغيلي',

        'shift_opened' => 'فتح وردية عمل جديدة',
        'shift_closed' => 'إغلاق وردية ومطابقة النقدية',

        'note_created' => 'تسجيل ملاحظة جديدة',
        'note_updated' => 'تعديل بيانات ملاحظة',
        'note_completed' => 'تحديد ملاحظة كمكتملة',
        'note_cancelled' => 'إلغاء ملاحظة',
    ];

    /**
     * Record an audit activity log entry.
     */
    public static function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
        ]);
    }
}

