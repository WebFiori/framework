/* global ajax, data */
ajax.setOnDisconnected(function () {
    this.props.vue.showDialog('Check your internet connection and try again.');
});
ajax.setOnSuccess({
    id:'Set Password',
    call:function() {
        return this.url === 'scheduler/apis/set-password';
    },
    callback:function() {
        if (this.jsonResponse) {
            window.location.href = data.base+'/scheduler/login';
        } else {
            this.props.vue.showDialog('Something went wrong. Try again.');
        }
    }
});
ajax.setOnSuccess({
    id:'Login',
    call:function() {
        return this.url === 'scheduler/apis/login';
    },
    callback:function() {
        if (this.jsonResponse) {
            window.location.href = data.base+'/scheduler/tasks';
        } else {
            this.props.vue.showDialog('Something went wrong. Try again.');
        }
    }
});
ajax.setOnClientError({
    id:'Login Failed',
    call:function() {
        return this.url === 'scheduler/apis/login';
    },
    callback:function() {
        if (this.jsonResponse) {
            this.props.vue.showDialog(this.jsonResponse.message);
        } else {
            this.props.vue.showDialog('Something went wrong. Try again.');
        }
    }
});
ajax.setBeforeAjax(function () {
    this.props.vue.loading = true;
});
ajax.setAfterAjax(function () {
    this.props.vue.loading = false;
});
ajax.setOnSuccess({
    id:'Logout',
    call:function() {
        return this.url === 'scheduler/apis/logout';
    },
    callback:function() {
        if (this.jsonResponse) {
            window.location.href = data.base+'/scheduler/login';
        } else {
            this.props.vue.showDialog('Something went wrong. Try again.');
        }
    }
});
ajax.setOnSuccess({
    id:'Get Tasks',
    call:function() {
        return this.url === 'scheduler/apis/get-tasks';
    },
    callback:function() {
        if (this.jsonResponse) {
            this.props.vue.tasks = this.jsonResponse.tasks;
        } else {
            this.props.vue.showDialog('Something went wrong. Try again.');
        }
    }
});
ajax.setOnSuccess({
    id:'After Force Execution',
    call:function() {
        return this.url === 'scheduler/apis/force-execution';
    },
    callback:function() {
        var vue = this.props.vue;
        vue.loading = false;
        vue.active_task.executing = false;
        var icon = 'mdi-check-decagram';
        var iconColor = 'green';
        
        if (this.status === 200 && this.jsonResponse) {
            var output = '';
            var info = this.jsonResponse['more-info'];

            if (info === undefined) {
                info = this.jsonResponse['more_info'];
            }

            if (info === undefined) {
                info = this.jsonResponse['moreInfo'];
            }
            for (var x = 0 ; x < info.log.length ; x++) {
                output += info.log[x]+'<br/>';
            }
            vue.output_dialog.output = output;

            if (info.failed.indexOf(vue.active_task.name) !== -1) {
                vue.output_dialog.failed = true;
                icon = 'mdi-alert-octagram';
                iconColor = 'red';
            } else {
                vue.output_dialog.failed = false;
            }
        } else {
            vue.output_dialog.output = this.response;
            vue.output_dialog.failed = true;
            icon = 'mdi-alert-octagram';
            iconColor = 'red';
        }
        vue.showDialog(this.jsonResponse.message, icon, iconColor);
    }
});
ajax.setOnServerError({
    id:'Server Error',
    call:true,
    callback:function() {
        if (this.jsonResponse) {
            if (this.jsonResponse.message) {
                this.props.vue.showDialog(this.jsonResponse.message);
            } else {
                this.props.vue.showDialog(this.status+' - Server Error.');
            }
        } else {
            this.props.vue.showDialog(this.status+' - Server Error.');
        }
    }
});
ajax.setOnClientError({
    id:'Client Error',
    call:true,
    callback:function() {
        if (this.jsonResponse) {
            if (this.jsonResponse.message) {
                this.props.vue.showDialog(this.jsonResponse.message);
            } else {
                this.props.vue.showDialog(this.status+' - Client Error.');
            }
        } else {
            this.props.vue.showDialog(this.status+' - Client Error.');
        }
    }
});
var app = new Vue({
    el:'#app',
    vuetify: new Vuetify(),
    data: {
        password:'',
        search:'',
        loading:false,
        tasks:[],
        expanded:[],
        tasks_table_headers:[
            {value:'info', text:''},
            {value:'name', text:'Job Name'},
            {value:'expression', text:'CRON Expression'},
            {value:'time.is_minute', text:'Is Minute'},
            {value:'time.is_hour', text:'Is Hour'},
            {value:'time.is_day_of_week', text:'Is Day of Week'},
            {value:'time.is_day_of_month', text:'Is Day of Month'},
            {value:'time.is_month', text:'Is Month'},
            {value:'actions', text:'Actions'},
        ],
        execution_dialog:{
            visible:false,
            title:'Message',
            message:'',
            icon_color:'',
            icon:''
        },
        output_dialog:{
            show:false,
            output:'',
            failed:false
        }
    },
    computed:{
        login_btn_disabled:function() {
            var pass = this.password+'';
            return pass.length < 6;
        },
        set_password_btn_disabled:function () {
            var pass = this.password+'';
            return pass.length < 6;
        }
    },
    mounted:function () {
        ajax.bind({
            vue:this
        });
        ajax.setBase(data.base);
        if (data.title === 'Scheduled Tasks') {
            this.loadTasks();
        }
    },
    methods:{
        forceExec:function (job) {
            this.active_task = job;
            var params = {
                'task-name':job.name
            };
            for(var x = 0 ; x < job.args.length ; x++) {
                var argVal = job.args[x].value;
                if (argVal !== undefined && argVal !== null && argVal.length !== 0) {
                    params[job.args[x].name] = argVal;
                }
            }
            ajax.setURL('scheduler/apis/force-execution');
            ajax.setMethod('post');
            ajax.setParams(params);
            ajax.send();
        },
        setPassword:function() {
            ajax.setURL('scheduler/apis/set-password');
            ajax.setMethod('post');
            ajax.setParams({
                password:this.password
            });
            ajax.send();
        },
        checkIfEnterHit:function(e) {
            if (e.keyCode === 13) {
                this.login();
            }
        },
        loadTasks:function() {
            ajax.setURL('scheduler/apis/get-tasks');
            ajax.setMethod('get');
            ajax.send();
        },
        dialogClosed:function() {
            this.execution_dialog.visible = false; 
        },
        showDialog(message, icon = 'mdi-information', iconColor = 'gray') {
            this.execution_dialog.message = message;
            this.execution_dialog.icon = icon;
            this.execution_dialog.icon_color = iconColor;
            this.execution_dialog.visible = true;
        },
        logout:function() {
            ajax.setURL('scheduler/apis/logout');
            ajax.setMethod('get');
            ajax.send();
        },
        login:function() {
            ajax.setURL('scheduler/apis/login');
            ajax.setMethod('post');
            ajax.setParams({
                password:this.password
            });
            ajax.send();
        }  
    }
});
