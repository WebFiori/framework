<?php
/**
 * Error page labels.
 */
const ERR_PAGE_LANG = array(
    'error'=>'خطأ',
    'go-home'=>'الرجوع للصفحة الرئيسية',
    'req-url'=>'الرابط المطللوب:'
);
/**
 * A constant that represents the error 403.
 */
const ERR_403 = array(
    'code'=>403,
    'type'=>'محظور',
    'message'=>'غير مسموح لك ان تعرض محتويات الرابط المطلوب'
);
/**
 * A constant that represents the error 404.
 */
const ERR_404 = array(
    'code'=>404,
    'type'=>'لم يتم العثور على المطلوب',
    'message'=>'الصفحة المطلوبة غير موجودة. نأسف على هذا الخطأ'
);
/**
 * A constant that represents the error 405.
 */
const ERR_405 = array(
    'code'=>405,
    'type'=>'الطريقة غير مسموحة',
    'message'=>'الطريقة المستخدمة غير مسموحة لإسترداد المورد المطلوب.'
);
/**
 * A constant that represents the error 408.
 */
const ERR_408 = array(
    'code'=>408,
    'type'=>'إنتهت مهلة الطلب',
    'message'=>'استغرق الطلب وقتا أطول من المتوقع وتم إعادة تعيين الاتصال.'
);
/**
 * A constant that represents the error 415.
 */
const ERR_415 = array(
    'code'=>415,
    'type'=>'نوع الوسائط غير مدعوم',
    'message'=>'صيغة حمولة الطلب غير مدعومة من قِبل الخادم.'
);
/**
 * A constant that represents the error 500.
 */
const ERR_500 = array(
    'code'=>500,
    'type'=>'خطأ في الخادم',
    'message'=>'خطأ غير معروف بالخادم. نأسف على هذا'
);
/**
 * A constant that represents the error 501.
 */
const ERR_501 = array(
    'code'=>501,
    'type'=>'لم تُنفذ',
    'message'=>'طريقة الطلب غير مدعومة مِن قِبل الخادم.'
);
/**
 * A constant that represents the error 505.
 */
const ERR_505 = array(
    'code'=>505,
    'type'=>'نُسخة HTTP غير مدعومة',
    'message'=>'نُسخة HTTP المُستخدمة من قِبل الطلب غير مدعومة.'
);
const NO_ERR = array(
    'code'=>0,
    'type'=>'',
    'message'=>''
);