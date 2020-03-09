window.onload = function(){
    var rtl = window.locale.dir === 'rtl';
    window.vue = new Vue({
        el: '#app',
        vuetify: new Vuetify({
            theme: {
                
            },
            rtl:rtl,
            lang:{
                current:window.locale.code
            }
        }),
        data: () => ({
            icons: [
              'mdi-facebook',
              'mdi-twitter',
              'mdi-google-plus',
              'mdi-linkedin',
              'mdi-instagram'
            ],
            drawer:false
        })
    });
};