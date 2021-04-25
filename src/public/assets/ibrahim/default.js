if (!window.data) {
    window.data = {
        dark:true,
        snackbars:[],
        darkTheme:{
            primary:'#8bc34a',
            secondary:'#4caf50',
            accent:'#795548',
            error:'#f44336',
            warning:'#ff9800', 
            info:'#607d8b',
            success:'#00bcd4'
        },
        lightTheme:{
            primary:'#8bc34a',
            secondary:'#4caf50',
            accent:'#795548',
            error:'#f44336',
            warning:'#ff9800', 
            info:'#607d8b',
            success:'#00bcd4'
        }
    };
}
new Vue({
    el: '#app',
    vuetify: new Vuetify({
        rtl:window.data.rtl,
        theme: {
            dark:window.data.dark,
            themes:{
                dark:window.data.darkTheme,
                light:window.data.lightTheme
            }
        }
    }),
    data:{
        loading:false,
        drawer:false,
        snackbars:window.data.snackbars
    },
    methods:{
        
    }
});