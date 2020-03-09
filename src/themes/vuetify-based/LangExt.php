<?php
namespace webfiori\theme\vutifyTheme;

use webfiori\entity\langs\Language;
/**
 * Description of LangExt
 *
 * @author Ibrahim
 */
class LangExt {
    /**
     * 
     * @param Language $translation
     */
    public static function extendLang($translation) {
        if($translation->getCode() == 'AR'){
            $translation->createAndSet('vuetify', [
                'noDataText'=>'لا توجد بيانات للعرض.',
                'sortBy'=>'ترتيب حسب',
                'close'=>'إغلاق'
            ]);
            $translation->createAndSet('vuetify/dataIterator', [
                'pageText'=>'{0}-{1} من {2}',
                'noResultsText'=>'لم يتم العثور على سجلات مطابقة.',
                'loadingText'=>'جاري تحميل العناصر...'
            ]);
            $translation->createAndSet('vuetify/dataTable', [
                'itemsPerPageText'=>'عدد السطور لكل صفحة:'
            ]);
            $translation->createAndSet('vuetify/dataTable/ariaLabel', [
                'sortDescending'=>'Sorted descending. Activate to remove sorting.',
                'sortAscending'=>'Sorted ascending. Activate to sort descending.',
                'sortNone'=>'Not sorted. Activate to sort ascending.'
            ]);
            $translation->createAndSet('vuetify/dataFooter', [
                'pageText'=>'{0}-{1} من {2}',
                'itemsPerPageText'=>'عدد العناصر بكل صفحة:',
                'itemsPerPageAll'=>'الجميع',
                'nextPage'=>'الصفحة التالية',
                'prevPage'=>'الصفحة السابقة',
                'firstPage'=>'الصفحة الأولى',
                'lastPage'=>'الصفحة الأخيرة'
            ]);
            $translation->createAndSet('datePicker', [
                'itemsSelected'=>'تم تحديد {0}'
            ]);
            $translation->createAndSet('vuetify/carousel', [
                'next'=>'الشكل التالي',
                'prev'=>'الشكل السابق'
            ]);
            $translation->createAndSet('vuetify/calendar', [
               'moreEvents'=>'{0} اكثر'
            ]);
            $translation->createAndSet('example/footer', [
                'get-connected'=>'تواصل معنا عن طريق شبكات التواصل الإجتماعية!',
                'copyright-notice'=>'جميع الحقوق محفوظة © '. date('Y')
            ]);
            $translation->createAndSet('side-menu', [
                'home'=>'الرئيسة',
                'account'=>'حسابي',
                'search'=>'بحث',
                'something-else'=>'شيء آخر'
            ]);
        }
        else{
            $translation->createAndSet('vuetify', [
                'noDataText'=>'No data available',
                'sortBy'=>'Sort by',
                'close'=>'Close'
            ]);
            $translation->createAndSet('vuetify/dataIterator', [
                'pageText'=>'{0}-{1} of {2}',
                'noResultsText'=>'No matching records found',
                'loadingText'=>'Loading items...'
            ]);
            $translation->createAndSet('vuetify/dataTable', [
                'itemsPerPageText'=>'Rows per page:'
            ]);
            $translation->createAndSet('vuetify/dataTable/ariaLabel', [
                'sortDescending'=>'Sorted descending. Activate to remove sorting.',
                'sortAscending'=>'Sorted ascending. Activate to sort descending.',
                'sortNone'=>'Not sorted. Activate to sort ascending.'
            ]);
            $translation->createAndSet('vuetify/dataFooter', [
                'pageText'=>'{0}-{1} of {2}',
                'itemsPerPageText'=>'Items per page:',
                'itemsPerPageAll'=>'All',
                'nextPage'=>'Next page',
                'prevPage'=>'Previous page',
                'firstPage'=>'First page',
                'lastPage'=>'Last page'
            ]);
            $translation->createAndSet('datePicker', [
                'itemsSelected'=>'{0} selected'
            ]);
            $translation->createAndSet('vuetify/carousel', [
                'next'=>'Previous visual',
                'prev'=>'Next visual'
            ]);
            $translation->createAndSet('vuetify/calendar', [
               'moreEvents'=>'{0} more'
            ]);
            $translation->createAndSet('example/footer', [
                'get-connected'=>'Stay in touch with us through social media!',
                'copyright-notice'=>'All Rights Reserved © '. date('Y')
            ]);
            $translation->createAndSet('side-menu', [
                'home'=>'Home',
                'account'=>'My Account',
                'search'=>'Search',
                'something-else'=>'Something Else'
            ]);
        }
    }
}
