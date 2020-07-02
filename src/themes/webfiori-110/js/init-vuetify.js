/* global vueLoaded */

window.onload = () => {
    //the 'window.data' object can be defined 
    //in PHP before the page is rendered.
    //same applies to 'window.locale'
    if(typeof window.computed !== 'object'){
        console.warn('window.computed is set to an empty object.');
        window.computed = {};
    }
    if(typeof window.methods !== 'object'){
        console.warn('window.methods is set to an empty object.');
        window.methods = {};
    }
    if(typeof window.data !== 'object'){
        console.warn('window.data is set to an empty object.');
        window.data = {};
    }
    if(typeof window.watch !== 'object'){
        console.warn('window.watch is set to an empty object.');
        window.watch = {};
    }
    if(typeof window.locale !== 'object'){
        console.warn('window.locale is not defined. Default is used.');
        window.locale = {
            dir:'rtl',
            'vuetify-defaults':{}
        };
    }
    var rtl = window.locale.dir === 'rtl';
    window.data.icons = [
        'mdi-facebook',
        'mdi-twitter',
        'mdi-google-plus',
        'mdi-linkedin',
        'mdi-instagram'
    ];
    window.data.drawer = false;
    window.vue = new Vue({
        el: '#app',
        vuetify: new Vuetify({
            theme: {
                themes: {
                    light: {
                        primary: "#009b77"
                    },
                    dark: {
                        primary: "#00779b"
                    }
                },
                dark: true
            },
            rtl: rtl,
            lang: {
                current: window.locale.code.toLocaleLowerCase(),
                locales: {
                    ar: window.locale['vuetify-defaults']
                }
            }
        }),
        mounted() {
            console.log('Vue initialized.');
            console.log('Calling the function "vueLoaded()"...');
            if(typeof vueLoaded === 'function'){
                vueLoaded();
            }
            else{
                console.warn('The function "vueLoaded()" is not defined.');
            }
            console.log('Done.');
        },
        data() {
            return window.data;
        },
        computed:window.computed,
        methods:window.methods,
        watch: window.watch
    });
};
/**
 * This function is used to show a snackbar which shows a text about something 
 * for the user.
 * @param {String} statusTxt The text that will be shown to the user.
 * @param {String} color the background color of the snackbar.
 * @returns {undefined}
 */
function setStatusText(statusTxt,color=""){
    if(window.vue.$data.snackbar){
        window.vue.$data.snackbar = false;
    }
    window.setTimeout(function(){
        window.vue.$data.snackbar = true;
    },200);
    window.vue.$data.statusText = statusTxt;
    window.vue.$data.statusColor = color;
}