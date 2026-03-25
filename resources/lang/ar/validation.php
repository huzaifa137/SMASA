<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Arabic)
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'يجب قبول الحقل :attribute.',
    'accepted_if' => 'يجب قبول الحقل :attribute عندما يكون :other هو :value.',
    'active_url' => 'الحقل :attribute ليس عنوان URL صالحاً.',
    'after' => 'يجب أن يكون الحقل :attribute تاريخاً بعد :date.',
    'after_or_equal' => 'يجب أن يكون الحقل :attribute تاريخاً بعد أو يساوي :date.',
    'alpha' => 'يمكن للحقل :attribute أن يحتوي على أحرف فقط.',
    'alpha_dash' => 'يمكن للحقل :attribute أن يحتوي على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يمكن للحقل :attribute أن يحتوي على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون الحقل :attribute مصفوفة.',
    'before' => 'يجب أن يكون الحقل :attribute تاريخاً قبل :date.',
    'before_or_equal' => 'يجب أن يكون الحقل :attribute تاريخاً قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute بين :min و :max حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على بين :min و :max عنصراً.',
    ],
    'boolean' => 'يجب أن تكون قيمة الحقل :attribute صواب أو خطأ.',
    'confirmed' => 'تأكيد الحقل :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'الحقل :attribute ليس تاريخاً صالحاً.',
    'date_equals' => 'يجب أن يكون الحقل :attribute تاريخاً يساوي :date.',
    'date_format' => 'لا يتطابق الحقل :attribute مع الصيغة :format.',
    'declined' => 'يجب رفض الحقل :attribute.',
    'declined_if' => 'يجب رفض الحقل :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن تكون قيم الحقول :attribute و :other مختلفة.',
    'digits' => 'يجب أن يحتوي الحقل :attribute على :digits أرقام.',
    'digits_between' => 'يجب أن يحتوي الحقل :attribute على بين :min و :max أرقام.',
    'dimensions' => 'أبعاد الصورة :attribute غير صحيحة.',
    'distinct' => 'الحقل :attribute له قيمة مكررة.',
    'email' => 'يجب أن يكون الحقل :attribute عنوان بريد إلكتروني صحيحاً.',
    'ends_with' => 'يجب أن ينتهي الحقل :attribute بأحد القيم التالية: :values.',
    'exists' => 'القيمة المحددة :attribute غير صحيحة.',
    'file' => 'يجب أن يكون الحقل :attribute ملفاً.',
    'filled' => 'يجب ملء الحقل :attribute.',
    'gt' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أكبر من :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute أطول من :value حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على أكثر من :value عناصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أكبر من أو تساوي :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute أطول من أو يساوي :value حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على :value عنصر أو أكثر.',
    ],
    'image' => 'يجب أن يكون الحقل :attribute صورة.',
    'in' => 'القيمة المحددة :attribute غير صحيحة.',
    'in_array' => 'الحقل :attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون الحقل :attribute عدداً صحيحاً.',
    'ip' => 'يجب أن يكون الحقل :attribute عنوان IP صحيحاً.',
    'ipv4' => 'يجب أن يكون الحقل :attribute عنوان IPv4 صحيحاً.',
    'ipv6' => 'يجب أن يكون الحقل :attribute عنوان IPv6 صحيحاً.',
    'json' => 'يجب أن يكون الحقل :attribute نصاً JSON صحيحاً.',
    'lt' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute أقصر من :value حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على أقل من :value عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من أو تساوي :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute أقصر من أو يساوي :value حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على :value عنصر أو أقل.',
    ],
    'max' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute أقل من أو تساوي :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute أقل من أو يساوي :max كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute أقل من أو يساوي :max حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على :max عنصر أو أقل.',
    ],
    'mimes' => 'يجب أن يكون الحقل :attribute ملفاً من نوع: :values.',
    'mimetypes' => 'يجب أن يكون الحقل :attribute ملفاً من نوع: :values.',
    'min' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute على الأقل :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :min حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على الأقل :min عنصر.',
    ],
    'multiple_of' => 'يجب أن تكون قيمة الحقل :attribute مضاعفاً للقيمة :value.',
    'not_in' => 'القيمة المحددة :attribute غير صحيحة.',
    'not_regex' => 'صيغة الحقل :attribute غير صحيحة.',
    'numeric' => 'يجب أن يكون الحقل :attribute قيمة رقمية.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون الحقل :attribute موجوداً.',
    'regex' => 'صيغة الحقل :attribute غير صحيحة.',
    'required' => 'الحقل :attribute مطلوب.',
    'required_if' => 'الحقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_unless' => 'الحقل :attribute مطلوب ما لم يكن :other هو :values.',
    'required_with' => 'الحقل :attribute مطلوب عند وجود :values.',
    'required_with_all' => 'الحقل :attribute مطلوب عند وجود :values.',
    'required_without' => 'الحقل :attribute مطلوب عند عدم وجود :values.',
    'required_without_all' => 'الحقل :attribute مطلوب عند عدم وجود أي من :values.',
    'same' => 'يجب أن تتطابق قيم الحقول :attribute و :other.',
    'size' => [
        'numeric' => 'يجب أن تكون قيمة الحقل :attribute مساوية لـ :size.',
        'file' => 'يجب أن يكون حجم الملف :attribute مساوياً لـ :size كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute مساوياً لـ :size حرفاً.',
        'array' => 'يجب أن يحتوي الحقل :attribute على :size عنصر.',
    ],
    'starts_with' => 'يجب أن يبدأ الحقل :attribute بأحد القيم التالية: :values.',
    'string' => 'يجب أن يكون الحقل :attribute نصاً.',
    'timezone' => 'يجب أن يكون الحقل :attribute منطقة زمنية صحيحة.',
    'unique' => 'قيمة الحقل :attribute مستخدمة بالفعل.',
    'uploaded' => 'فشل تحميل الملف :attribute.',
    'url' => 'صيغة الحقل :attribute غير صحيحة.',
    'uuid' => 'يجب أن يكون الحقل :attribute UUID صحيحاً.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute names with
    | something more reader friendly and appropriate, such as E-Mail Address
    | instead of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
