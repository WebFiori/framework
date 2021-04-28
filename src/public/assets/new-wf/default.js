
if (!window.data) {
    window.data = {
        dark:true,
        snackbar:{
            color:'',
            text:'',
            visible:false
        }
    };
}
window.app = new Vue({
    el: '#app',
    vuetify: new Vuetify({
        rtl:window.data.is_rtl,
        theme: {
            //dark:window.data.dark,
            themes: {
                light: {
                    
                }
            }
        }
    }),
    data:{
        loading:false,
        drawer:false,
        snackbar:window.data.snackbar,
        search_results:[],
        methods_search_results:[],
        docs_search_results:[],
        search_val:'',
        show_search_menu:false,
        mini:false
    },
    methods:{
        search:function() {
            if (this.search_val.trim().length !== 0) {
                search(this.search_val);
                this.showMenu = true;
            } else {
                this.showMenu = false;
            }
        }
    }
});
function showSnackbar(message, color = '') {
    window.app.snackbar.color = color;
    window.app.snackbar.text = message;
    window.app.snackbar.visible = true;
}