<?php

return [

    /*
    |--------------------------------------------------------------------------
    | عبارات التحقق (Validation Language Lines)
    |--------------------------------------------------------------------------
    */

    'required' => 'حقل :attribute مطلوب.',
    'string' => 'حقل :attribute يجب أن يكون نصاً.',
    'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
    'boolean' => 'حقل :attribute يجب أن يكون صحيحاً أو خاطئاً.',
    'max' => [
        'string' => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',
        'numeric' => 'حقل :attribute يجب ألا يتجاوز :max.',
    ],
    'min' => [
        'string' => 'حقل :attribute يجب أن يحتوي على الأقل :min حروف.',
        'numeric' => 'حقل :attribute يجب ألا يقل عن :min.',
    ],
    'gte' => [
        'numeric' => 'حقل :attribute يجب أن يكون أكبر من أو يساوي :value.',
    ],
    'gt' => [
        'numeric' => 'حقل :attribute يجب أن يكون أكبر من :value.',
    ],
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور الحالية غير صحيحة.',
    'unique' => 'قيمة :attribute مستخدمة بالفعل.',
    'exists' => ':attribute المحدد غير موجود.',
    'in' => ':attribute المحدد غير صالح.',

    'attributes' => [
        'username' => 'اسم المستخدم',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'new_password' => 'كلمة المرور الجديدة',
        'name' => 'الاسم',
        'role' => 'الدور والوظيفة',
        'category_id' => 'القسم الرئيسي',
        'subcategory_id' => 'القسم الفرعي',
        'purchase_price' => 'سعر الشراء (التكلفة)',
        'selling_price' => 'سعر البيع',
        'stock_quantity' => 'الكمية في المخزون',
        'minimum_stock' => 'حد التنبيه الأدنى',
        'description' => 'الوصف والتفاصيل',
        'is_active' => 'حالة التفعيل',
    ],

];
