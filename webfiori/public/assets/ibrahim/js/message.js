/**
 * Shows a snackbar with a message.
 * 
 * @param {String} txt The message that will be shown by the snackbar.
 * 
 * @param {String} color A string that can be used as snackbar color. Possible 
 * values: 'red', 'green', 'orange' or a hex value.
 * 
 * @param {boolean} perminant If set to true, the message will stay visible the 
 * whole time.
 * 
 * @param {String} icon An optional Material design icon to append on the snackbar.
 * 
 * @returns {undefined}
 */
function showMsg(txt = '', color = '', perminant = false, icon = 'mdi-information') {
    var sn = window.data.snackbars[0];
    var index = 0;
    while(sn.snackbar === true && index < window.data.snackbars.length) {
        index++;
        sn = window.data.snackbars[index];
    }
    sn.statusText = txt;
    sn.statusColor = color;
    if (icon !== null && icon !== undefined) {
        sn.icon = icon.trim();
    } else {
        sn.icon = 'mdi-information';
    }
    if (perminant === true) {
        sn.snackbarTimeout = -1;
    } else {
        sn.snackbarTimeout = 5000;
    }
    sn.snackbar = true;
}
function showDisconMsg() {
    if (window.i18n) {
        showMsg(window.i18n.vars.general.error['connection-err'],'red', true);
    } else {
        if (window.data.rtl) {
            showMsg('الرجاء التحقق من اتصالك بالإنترنت!','red', true);
        } else {
            showMsg('Please check your internet connection!','red', true);
        }
    }
}
function showUnkownErrMsg() {
    if (window.i18n) {
        showMsg(window.i18n.vars.general.error['unkown-err'], 'red', true);
    } else {
        if (window.data.rtl) {
            showMsg('حصل خطأ غير محدد!','red', true);
        } else {
            showMsg('Unkown error!','red', true);
        }
    }
}