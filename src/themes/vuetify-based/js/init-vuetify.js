window.onload = () => {
    //the 'window.data' object can be defined 
    //in PHP before the page is rendered.
    //same applies to 'window.locale'
    if(window.data === undefined){
        console.warn('window.data is undefined. Default is used.');
        window.data = {};
    }
    if(window.locale === undefined){
        console.warn('window.locale is undefined. Default is used.');
        window.locale = {
            dir:'ltr',
            code:'en'
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
        computed:{
            //this can be accessed in PHP and binde it to vutify component 
            // as follows: 
            // :label="languageVars.general.something"
            languageVars(){
                return window.locale;
            }
        }
    });
};
