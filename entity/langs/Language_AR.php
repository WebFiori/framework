<?php
/**
 * A language file that represents Arabic language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
const LANGUAGE = array(
    'general'=>array(
        'wait'=>'الرجاء الإنتظار... 🙂',
        'loading'=>'جاري التحميل...'
    ),
    'pages'=>array(
        'activate-account'=>array(
            'title'=>'تفعيل حساب المستخدم',
            'description'=>'صفحة تفعيل الحساب',
            'success'=>'Account Activated!',
            'labels'=>array(
                'main'=>'تفعيل حسابك بالنظام',
                'activation-token'=>'رمز التفعيل:'
            ),
            'placeholders'=>array(
                'activation-token'=>' أدخل رمز تفعيل الحساب هنا.'
            ),
            'actions'=>array(
                'activate'=>'تفعيل'
            ),
            'errors'=>array(
                'inncorect-token'=>'رمز التفعيل غير صحيح!'
            )
        ),
        'login'=>array(
            'title'=>'تسجيل الدخول',
            'description'=>'تسجيل الدخول إلى النظام.',
            'success'=>'تم تسجيل الدخول.',
            'keep-me-logged'=>'إحتفظ بتسجيل دخولي لمدة إسبوع.',
            'actions'=>array(
                'login' => 'تسجيل الدخول',
            ),
            'labels'=>array(
                'main'=>'تسجيل الدخول إلى النظام',
                'username' => 'إسم المستخدم او البريد الإلكتروني:',
                'password' => 'كلمة المرور:',
            ),
            'placeholders'=>array(
                'username' => 'أدخل إسم المستخدم او البريد الإلكتروني هنا.',
                'password' => 'أدخل كلمة المرور هنا.',
            ),
            'errors'=>array(
                'something-wrong'=>'حطل خطأ غير معروف. الرجاء المحاولة مجددا. 😲',
                'incorrect-login-params' => 'إسم المستخدم أو البريد الإلكتروني او كلمة المرور خطأ.',
                'err-missing-pass' => 'الرجاء إدخال كلمة المرور.',
                'err-missing-username' => 'الرجاء إدخال إسم المستخدم.'
            )
        ),
        'home'=>array(
            'title'=>'الصفحة الرئيسة',
            'description'=>'هذه الصفحة الرئيسة.'
        ),
        'sys-info'=>array(
            'title'=>'معلومات النظام',
            'description'=>'حول النظام.'
        ),
        'profile'=>array(
            'title'=>'ملفي الشخصي',
            'description'=>'معاينة ملفك الشخصي.',
            'labels'=>array(
                'username'=>'إسم المستخدم:',
                'display-name'=>'إسم العرض:',
                'email'=>'البريد الإلكتروني:',
                'status'=>'حالة الحساب:',
                'reg-date'=>'تاريخ التسجيل: ',
                'last-login'=>'تاريخ آخر تسجيل دخول: ',
                'access-level'=>'مستوى الوصول: ',
                'activation-token'=>'رمز تفعيل الحساب: ',
                'actions'=>'عمليات الحساب: ',
                'update-email'=>'تحديث البريد الإلكتروني',
                'update-disp-name'=>'تحديث إسم العرض',
                'update-password'=>'تحديث كلمة المرور'
            )
        ),
        'update-disp-name'=>array(
            'title'=>'تحديث إسم العرض',
            'description'=>'تحديث إسم الهرض خاصتك.',
            'labels'=>array(
                'empty-name'=>'الرجاء كتابة إسم العرض الجديد.',
                'disp-name'=>'الإسم الجديد:',
                'update'=>'تحديث إسم العرض',
                'updated'=>'<b style="color:green">تم تحديث إسم العرض</b>'
            )
        ),
        'update-email'=>array(
            'title'=>'تحديث البريد الإلكتروني',
            'description'=>'تحديث البريد الإلكتروني.',
            'labels'=>array(
                'empty-email'=>'الرجاء كتابة البريد الإلكتروني الجديد.',
                'email'=>'البريد الإلكتروني الجديد:',
                'update'=>'تحديث البريد',
                'updated'=>'<b style="color:green">تم التحديث بنجاح.</b>'
            )
        ),
        'update-pass'=>array(
            'title'=>'تحديث كلمة المرور',
            'description'=>'تحديث كلمة مرور الحساب.',
            'labels'=>array(
                'old-pass'=>'كلمة المرور القديمة:',
                'new-pass'=>'كلمة المرور الجديدة:',
                'conf-pass'=>'أعد كتابة كلمة المرور الجديدة:',
                'update'=>'تحديث كلمة المرور',
                'pass-missmatch'=>'كلمة المرور لا تتطابق!',
                'empty-old-pass'=>'الرجاء كتابة كلمة المرور القديمة.',
                'empty-new-password'=>'الرجاء كتابة كلمة المرور الجديدة.',
                'incorrect-old-pass'=>'كلمة المرور القديمة خاطئة!',
                'updated'=>'<b style="color:green">تم تحديث كلمة المرور.</b>'
            )
        ),
        'view-users'=>array(
            'title'=>'مستخدمي النظام',
            'description'=>'قائمة بأسماء مستخدمي النظام.',
            'labels'=>array(
                'username'=>'إسم المستخدم',
                'disp-name'=>'إسم العرض',
                'email'=>'البريد الإلكتروني',
                'status'=>'حالة الحساب',
                'reg-date'=>'تاريخ التسجيل',
                'last-login'=>'تاريخ آخر تسجيل دخول'
            )
        ),
        'register'=>array(
            'title'=>'إنشاء حساب مستخدم جديد',
            'description'=>'Creating new profile.',
            'labels'=>array(
                'username'=>'إسم المستخدم:',
                'password'=>'كلمة المرور:',
                'conf-pass'=>'أعد كتابة كلمة المرور:',
                'email'=>'البريد الإلكتروني:',
                'disp-name'=>'إسم العرض:',
                'reg'=>'تسجيل',
                'acc-lvl'=>'مستوى الوصول:'
            ),
            'errors'=>array(
                'missing-acc-lvl'=>'الرجاء إختيار مستوى الوصول!',
                'missing-username'=>'الرجاء كتابة إسم المستخدم.',
                'missing-pass'=>'الرجاء كتابة كلمة المرور.',
                'pass-missmatch'=>'كلمة المرور لا تتطابق.',
                'missing-email'=>'الرجاء كتابة البريد الإلكتروني.'
            )
        ),
    ),
    'aside'=>array(
        'logout'=>'تسجيل الخروج',
        'home'=>'الصفحة الرئيسة',
        'profile'=>'ملف المستخدم',
        'add-user'=>'إنشاء مستخدم جديد',
        'view-users'=>'معاينة المستخدمين',
        'sys-info'=>'معلومات النظام'
    ),
    'api-messages'=>array(
        
    )
);