<?php
/**
 * A language file that represents Arabic language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
const LANGUAGE = array(
    'dir'=>'ltr',
    'general'=>array(
        'wait'=>'الرجاء الإنتظار... 🙂',
        'loading'=>'جاري التحميل...',
        'next'=>'التالي',
        'prev'=>'السابق',
        'skip'=>'تخطي',
        'connected'=>'تم الإتصال بنجاح.',
        'disconnected'=>'الرجاء التحقق من إتصالك بالإنترنت.'
    ),
    'pages'=>array(
        'setup'=>array(
            'setup-steps'=>array(
                'welcome'=>'صفحة الترحيب',
                'database-setup'=>'تهيئة قاعدة البيانات',
                'email-account'=>'البريد الإلكتروني',
                'admin-account'=>'حساب المسؤول',
                'website-config'=>'تهيئة الموقع',
                'finish'=>'الإنهاء'
            ),
            'email-account'=>array(
                'labels'=>array(
                    'name'=>'إسم الحساب:',
                    'server-address'=>'عنوان الخادم:',
                    'email-address'=>'عنوان البريد الإلكتروني:',
                    'username'=>'إسم المستخدم:',
                    'password'=>'كلمة المرور:',
                    'port'=>'رقم بوابة الخادم:',
                    'check-connection'=>'تفقد الإتصال',
                    'connected'=>'تم الإتصال بنجاح!'
                ),
                'placeholders'=>array(
                    'name'=>'إسم يعكس هدف الحساب (مثلا, إشعارات الموقع)',
                    'server-address'=>'عنوان خادم إرسال البريد الإلكتروني (مثلا, mail.example.com)',
                    'email-address'=>'my_address@example.com',
                    'username'=>'إسم المستخدم لتسجيل الدخول للخادم.',
                    'password'=>'كلمة مرور الحساب.',
                    'port'=>'25'
                ),
                'status'=>array(
                    'checking-connection'=>'جاري تفقد معلومات الإتصال...',
                ),
                'errors'=>array(
                    'inv_mail_host_or_port'=>'عنوان خادم البريد الإلكتروني اوالبوابة غير صحيحين.',
                    'inv_username_or_pass'=>'إسم المستخدم او كلمة المرور غير صحيحين.'
                ),
                'help'=>array(
                    'h-1'=>'In this step, we need from you to give us the information of '
                    . 'the email account hat we are going to use in order to send notifications. '
                    . 'We need from you to give us SMTP server information (Port and address) in addition to '
                    . 'SMTP account information.',
                    'h-2'=>'If you don\' have the information, you can skip this step.'
                )
            ),
            'welcome'=>array(
                'title'=>'اهلاً و سهلاً',
                'description'=>'',
                'help'=>array(
                    'h-1'=>'أهلا بك في تنصيب البرنامج. بما أن هذه هي المرة الأولى '
                    . 'اللتي تستعمل فيها البرنامج, يجب عليك إعداد بعض الأشياء.',
                    'h-2'=>'أولاً سوف نحتاج منك ان تقوم بتزويدنا بمعلومات قاعدة البيانات. ثُم نريد من تزويدنا بمعلومات حساب البريد الإلكتروني اللذي سوف نستعمله لإرسال تنبيهات النظام. بعد هذا, عليك القيام بإنشاء حساب المسؤول. آخر خطوة سوف تكون القيام بعملية إعداد بعض الأشياء الأساسية اللتي تتعلق بالموقع.',
                    'h-3'=>'قبل إكمال عملية الإعداد, الرجاء التأكد من وجود التالي:',
                    'h-4'=>'معلومات حساب قاعدة بيانات MySQL.',
                    'h-5'=>'معلومات حساب البريد الإلكتروني اللذي سوف نستعمله لإرسال التنبيهات.',
                )
            ),
            'database-setup'=>array(
                'title'=>'تهيئة قاعدة البيانات',
                'description'=>'',
                'labels'=>array(
                    'username'=>'إسم المستخدم:',
                    'host'=>'عنوان المُضيف:',
                    'password'=>'كلمة المرور:',
                    'database-name'=>'إسم قاعدة البيانات:',
                    'check-connection'=>'تفقد الإتصال',
                    'connected'=>'تم الإتصال بنجاح!'
                ),
                'placeholders'=>array(
                    'username'=>'أدخل إسم مستخدم قاعدة البيانات.',
                    'host'=>'localhost',
                    'password'=>'كلمة مرور مستخدم قاعدة البيانات.',
                    'database-name'=>'إسم قاعدة البيانات.',
                ),
                'status'=>array(
                    'checking-connection'=>'جاري تفقد معلومات الإتصال...',
                ),
                'help'=>array(
                    'h-1'=>'في هذه الخطوة, مطلوب منك تزويدنا بمعلومات قاعدة بيانات MySQL. '
                    . 'في البداية, نحتاج منك أن تُعطينا عنوان مُضيف قاعدة البيانات. قد يكون عنوان المُضيف '
                    . 'مجرد رابط او قد يكون عنوان IP. إذا كانت قاعدة البيانات موجودة على نفس الخادم الموجود '
                    . 'به الموقع, إستخدم localhost.',
                    'h-2'=>'الشيء الثاني اللذي نحتاجه هو حساب المستخدم اللذي سوف يتم إستخدامه للإتصال '
                    . 'بقاعدة البيانات. هذا الحساب يجب ان تكون لديه جميع التصاريح على قاعدة البيانات.',
                    'h-3'=>'آخر شيء نحتاجه منك هو إسم قاعدة البيانات اللتي سوف يتم إستخدامها.'
                ),
                'errors'=>array(
                    2002=>'الرجاء التأكد من صحة إسم المضيف و أنه يحتوي على خادم MySQL.',
                    1045=>'الرجاء التأكد من صحة إسم المستخدم و كلمة المرور.',
                    1044=>'ليست لديك الأُوذُنات الكافية لدخول قاعدة البيانات المُعطاة..',
                    1049=>'الرجاء التأكد من صحة إسم قاعدة البيانات.',
                    10000=>'قاعدة البيانات المُختارة ليست فارغة. يجب ان تكون قاعدة البيانات خالية من الجداول.'
                )
            ),
            'admin-account'=>array(
                'title'=>'إنشاء حساب مسؤول النظام',
                'description'=>'',
                'help'=>''
            ),
            'website-config'=>array(
                'title'=>'تهيئة الموقع',
                'description'=>'',
                'help'=>''
            ),
            'finish'=>array(
                'title'=>'إنهاء',
                'description'=>''
            )
        ),
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
            'actions'=>array(
                'login' => 'تسجيل الدخول',
            ),
            'labels'=>array(
                'keep-me-logged'=>'إحتفظ بتسجيل دخولي لمدة إسبوع.',
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