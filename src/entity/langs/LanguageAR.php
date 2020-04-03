<?php
/*
 * The MIT License
 *
 * Copyright 2019 Ibrahim, WebFiori Framework.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace webfiori\entity\langs;

/**
 * A class that contain some of the common language labels in Arabic.
 * So far, the class has the following variables:
 * <ul>
 * <li>general/week-day: The names of week days. 'd1' for Monday to 'd7' for Sunday.</li>
 * <li>general/g-month: Names of months in Gregorian calendar. 'm1' for January 
 * up to 'm12' for December.</li>
 * <li>general/action: A set of common actions that are usually performed 
 * by users. The actions are:
 * <ul>
 * <li>cancel</li>
 * <li>back</li>
 * <li>save</li>
 * <li>remove</li>
 * <li>delete</li>
 * <li>print</li>
 * <li>next</li>
 * <li>previous</li>
 * <li>skip</li>
 * <li>connect</li>
 * <li>finish</li>
 * </ul>
 * </li>
 * <li>general/status: A set of common statuses for application elements 
 * after performing specific action. The actions are:
 * <ul>
 * <li>wait</li>
 * <li>loading</li>
 * <li>checking</li>
 * <li>validating</li>
 * <li>loaded</li>
 * <li>saving</li>
 * <li>saved</li>
 * <li>removing</li>
 * <li>removed</li>
 * <li>deleting</li>
 * <li>deleted</li>
 * <li>printing</li>
 * <li>printed</li>
 * <li>connecting</li>
 * <li>connected</li>
 * <li>disconnected</li>
 * </ul>
 * </li>
 * <li>general/error: A set of common error messages The errors are:
 * <ul>
 * <li>db-error</li>
 * <li>db-connect-err</li>
 * <li>save</li>
 * <li>remove</li>
 * <li>delete</li>
 * <li>print</li>
 * <li>connect</li>
 * </ul>
 * </li>
 * <li>general/http-codes: A set that contains most common HTTP codes. 
 * inside each code, there are 3 items:
 * <ul>
 * <li>code: The actual code such as 200 or 404 as an integer.</li>
 * <li>type: The type of the code such as 'Ok' or 'Not Authorized'.</li>
 * <li>message: The meaning of the code in more details.</li>
 * </ul>
 * So far, the available codes are:
 * <ul>
 * <li>200</li>
 * <li>201</li>
 * <li>400</li>
 * <li>401</li>
 * <li>403</li>
 * <li>404</li>
 * <li>405</li>
 * <li>408</li>
 * <li>415</li>
 * <li>500</li>
 * <li>501</li>
 * <li>505</li>
 * </ul>
 * </li>
 * <ul>
 * @version 1.0
 * @author Ibrahim
 */
class LanguageAR extends Language {
    public function __construct() {
        parent::__construct('rtl', 'AR', true);
        $this->createAndSet('general/http-codes/200', [
            'code' => 200,
            'type' => 'OK',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/201', [
            'code' => 201,
            'type' => 'تم الإنشاء',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/400', [
            'code' => 400,
            'type' => 'طلب سيء',
            'message' => 'تعذر على الخادم فهم الطلب بسبب عدم صحة بناء الجملة.'
        ]);
        $this->createAndSet('general/http-codes/401', [
            'code' => 401,
            'type' => 'غير مخول',
            'message' => 'غير مصرح لك بعرض المورد المحدد.'
        ]);
        $this->createAndSet('general/http-codes/403', [
            'code' => 403,
            'type' => 'محظور',
            'message' => 'غير مسموح لك بعرض محتوى المورد المطلوب.'
        ]);
        $this->createAndSet('general/http-codes/404', [
            'code' => 404,
            'type' => 'غير موجود',
            'message' => 'لا يمكن العثور على المورد المطلوب.'
        ]);
        $this->createAndSet('general/http-codes/405', [
            'code' => 405,
            'type' => 'الطريقة غير مسموحة',
            'message' => 'الطريقة اللتي تم إستخدامها للحصول على المورد غير مسموحة.'
        ]);
        $this->createAndSet('general/http-codes/408', [
            'code' => 408,
            'type' => 'إنتهت مهلة الطلب',
            'message' => ''
        ]);
        $this->createAndSet('general/http-codes/415', [
            'code' => 415,
            'type' => 'نوع الوسائط غير مدعوم',
            'message' => 'تنسيق حمولة الطلب غير مدعوم من قِبل الخادم.'
        ]);
        $this->createAndSet('general/http-codes/500', [
            'code' => 500,
            'type' => 'خطأ بالخادم',
            'message' => 'خطأ داخلي بالخادم.'
        ]);
        $this->createAndSet('general/http-codes/501', [
            'code' => 501,
            'type' => 'لم تُنفذ',
            'message' => 'طريقة الطلب غير مدعومة.'
        ]);
        $this->createAndSet('general/http-codes/505', [
            'code' => 505,
            'type' => 'نسخة HTTP غير مدعومة',
            'message' => 'نسخة HTTP المُستخدمة في الطلب غير مدعومة من قِبل الخادم.'
        ]);

        $this->createAndSet('general/action', [
            'cancel' => 'إلغاء',
            'back' => 'رجوع',
            'save' => 'حفظ',
            'remove' => 'إزالة',
            'delete' => 'حذف',
            'print' => 'طباعة',
            'connect' => 'إتصال',
            'next' => 'التالي',
            'previous' => 'السابق',
            'skip' => 'تخطي',
            'finish' => 'إنهاء'
        ]);

        $this->createAndSet('general/error', [
            'db-error' => 'خطأ في قاعدة البيانات.',
            'db-connect-err' => 'غير قادر على الإتصال بقاعدة البيانات.'
        ]);

        $this->createAndSet('general/status', [
            'wait' => 'الرجاء الإنتظار للحظة...',
            'loading' => 'جاري التحميل...',
            'checking' => 'جاري التحقق...',
            'validating' => 'جاري التحقق من الصحة...',
            'loaded' => 'تم التحميل.',
            'saving' => 'جاري الحفظ...',
            'saved' => 'تم الحفظ.',
            'removing' => 'جاري الإزالة...',
            'removed' => 'تمت الإزالة.',
            'deleting' => 'جاري الحذف...',
            'deleted' => 'تم الحذف.',
            'printing' => 'جاري الطباعة...',
            'printed' => 'تمت الطباعة.',
            'connecting' => 'جاري الإتصال...',
            'connected' => 'تم الإتصال.',
            'disconnected' => 'غير متصل.'
        ]);

        $this->createAndSet('general/error', [
            'save' => 'غير قادر على الحفظ!',
            'remove' => 'غير قادر على الإزالة!',
            'delete' => 'غير قادر على الحذف!',
            'print' => 'غير قادر على الطباعة !',
            'connect' => 'غير قادر على الإتصال!'
        ]);

        $this->createAndSet('general/week-day', [
            'd7' => 'الأحد',
            'd1' => 'الأثنين',
            'd2' => 'الثلاثاء',
            'd3' => 'الأربعاء',
            'd4' => 'الخميس',
            'd5' => 'الجمعة',
            'd6' => 'السبت',
        ]);

        $this->createAndSet('general/g-month', [
            'm1' => 'يناير',
            'm2' => 'فبراير',
            'm3' => 'مارس',
            'm4' => 'أبريل',
            'm5' => 'مايو',
            'm6' => 'يونيو',
            'm7' => 'يوليو',
            'm8' => 'اغسطس',
            'm9' => 'سبتمبر',
            'm10' => 'أكتوبر',
            'm11' => 'نوفمبر',
            'm12' => 'ديسمبر',
        ]);

        $this->createAndSet('general/i-month', [
            'm1' => 'محرم',
            'm2' => 'صفر',
            'm3' => 'ربيع اول',
            'm4' => 'ربيع ثاني',
            'm5' => 'جُمادى الأول',
            'm6' => 'جُمادى الآخرة',
            'm7' => 'رجب',
            'm8' => 'شعبان',
            'm9' => 'رمضان',
            'm10' => 'شوال',
            'm11' => 'ذو القُعدة',
            'm12' => 'ذو الحُحجة',
        ]);
    }
}
