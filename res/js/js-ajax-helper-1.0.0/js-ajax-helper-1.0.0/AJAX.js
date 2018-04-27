
"use strict";
Object.defineProperties(AJAX,{
    'META':{
        writable:false,
        value:{}
    },
    'CALLBACK_POOLS':{
        /**
        * Names of pools of events.
        * @type Array
        */
        value:['servererror','clienterror','success','connectionlost'],
        writable:false
    },
    'XMLHttpFactories':{
        /**
        * Array of functions used to create XMLHttpRequest object.
        * @type Array
        */
        value:[
            function (){return new XMLHttpRequest();},
            function (){return new ActiveXObject("Microsoft.XMLHTTP");},
            function (){return new ActiveXObject("MSXML2.XMLHTTP.3.0");}
        ],
        writable:false
    },
    createXhr:{
        /**
        * A factory function used to create XHR object for diffrent browsers.
        * @returns {Mixed} False in case of failure. Other than that, it will 
        * return XHR object that can be used to send AJAX.
        */
        value:function createXhr(){
            for(var i = 0 ; i < AJAX.XMLHttpFactories.length ; i++){
                try{
                    return AJAX.XMLHttpFactories[i]();
                }
                catch(e){

                }
            }
            return false;
        },
        wriable:false
    }
});
Object.defineProperties(AJAX.META,{
    VERSION:{
        value:'0.0.7',
        writable:false
    },
    REALSE_DATE:{
        value:'04/01/2018',
        writable:false
    },
    CONTRIBUTORS:{
        value:[
            {
                name:'Ibrahim Ali BinAlshikh',
                email:'ibinshikh@hotmail.com'
            }
        ],
        writable:false
    }
});
/**
 * A class that can be used to simplfy AJAX requests.
 * @version 0.0.5
 * @author Ibrahim BinAlshikh <ibinshikh@hotmail.com>
 * @constructor
 * @param {Object} config AJAX configuration.
 * @returns {AJAX}
 */
function AJAX(config={
    method:'get',
    url:'',
    'enable-log':false,
    enabled:true
}){
    /**
     * Request method.
     */
    this.method = 'GET';
    /**
     * The URL of AJAX request
     */
    this.url = '';
    /**
     * Any parameters to send with the request.
     */
    this.params = '';
    /**
     * Enable or disable AJAX. used to ristrict access.
     */
    this.enabled = true;
    /**
     * Server response after processing the request.
     */
    this.serverResponse = null;
    /**
     * A callback function to call in case of file upload is completed. 
     * Similar to onreadystatechange.
     * @returns {undefined}
     */
    this.onload = function(){};
    this.onprogress = function(e){
        if (e.lengthComputable) {
            var percentComplete = (e.loaded / e.total) * 100;
            console.info('Uploaded: '+percentComplete+'%');
        }
    };
    /**
     * A pool of functions to call in case of internet connection lost.
     */
    this.onconnectionlostpool = [
        {
            'id':0,
            'call':true,
            'func':function(){
                console.info('AJAX: Connection lost. Status: '+this.status);
            }
        }
    ];
    /**
     * A pool of functions to call in case of successful request.
     */
    this.onsuccesspool = [
        {
            'id':0,
            'call':true,
            'func':function(){
                console.info('AJAX: Success '+this.status);
            }
        }
    ];
    /**
     * A pool of functions to call in case of server error.
     */
    this.onservererrorpool = [
        {
            'id':0,
            'call':true,
            'func':function(){
                console.info('AJAX: Server Error '+this.status);
            }
        }
    ];
    /**
     * A pool of functions to call in case of client error.
     */
    this.onclienterrorpool = [
        {
            'id':0,
            'call':true,
            'func':function(){
                console.info('AJAX: Client Error '+this.status);
            }
        }
    ];
    Object.defineProperty(this,'onreadystatechange',{
        value:function(){
            if(this.readyState === 0){
                this.log('AJAX: Ready State = 0 (UNSENT)','info');
            }
            else if(this.readyState === 1){
                this.log('AJAX: Ready State = 1 (OPENED)','info');
            }
            else if(this.readyState === 2){
                this.log('AJAX: Ready State = 2 (HEADERS_RECEIVED)','info');
            }
            else if(this.readyState === 3){
                this.log('AJAX: Ready State = 3 (LOADING)','info');
            }
            else if(this.readyState === 4 && this.status === 0){
                this.log('AJAX: Ready State = 4 (DONE)','info');
                for(var i = 0 ; i < this.onconnectionlostpool.length ; i++){
                    this.onconnectionlostpool[i].status = this.status;
                    this.onconnectionlostpool[i].response = this.responseText;
                    this.onconnectionlostpool[i].xmlResponse = this.responseXML;
                    this.onconnectionlostpool[i].jsonResponse = null;
                    if(this.onconnectionlostpool[i].call === true){
                        this.onconnectionlostpool[i].func();
                    }
                }
            }
            else if(this.readyState === 4 && this.status >= 200 && this.status < 300){
                this.log('AJAX: Ready State = 4 (DONE)','info');
                try{
                    var jsonResponse = JSON.parse(this.responseText);
                }
                catch(e){
                    this.log('Unable to convert response into JSON object.','warning');
                    this.log('JSON DATA is set to \'null\'.','warning');
                    var jsonResponse = null;
                }
                for(var i = 0 ; i < this.onsuccesspool.length ; i++){
                    this.onsuccesspool[i].status = this.status;
                    this.onsuccesspool[i].response = this.responseText;
                    this.onsuccesspool[i].xmlResponse = this.responseXML;
                    this.onsuccesspool[i].jsonResponse = jsonResponse;
                    if(this.onsuccesspool[i].call === true){
                        this.onsuccesspool[i].func();
                    }
                }
            }
            else if(this.readyState === 4 && this.status >= 400 && this.status < 500){
                this.log('AJAX: Ready State = 4 (DONE)','info');
                try{
                    var jsonResponse = JSON.parse(this.responseText);
                }
                catch(e){
                    this.log('Unable to convert response into JSON object.','warning');
                    this.log('JSON DATA is set to \'null\'.','warning');
                    var jsonResponse = null;
                }
                for(var i = 0 ; i < this.onclienterrorpool.length ; i++){
                    this.onclienterrorpool[i].status = this.status;
                    this.onclienterrorpool[i].response = this.responseText;
                    this.onclienterrorpool[i].xmlResponse = this.responseXML;
                    this.onclienterrorpool[i].jsonResponse = jsonResponse;
                    if(this.onclienterrorpool[i].call === true){
                        this.onclienterrorpool[i].func();
                    }
                }
            }
            else if(this.readyState === 4 && this.status >= 300 && this.status < 400){
                this.log('AJAX: Ready State = 4 (DONE)','info');
                this.log('Redirect','info',true);
            }
            else if(this.readyState === 4 && this.status >= 500 && this.status < 600){
                this.log('AJAX: Ready State = 4 (DONE)','info');
                try{
                    var jsonResponse = JSON.parse(this.responseText);
                }
                catch(e){
                    this.log('Unable to convert response into JSON object.','warning');
                    this.log('JSON DATA is set to \'null\'.','warning');
                    var jsonResponse = null;
                }
                for(var i = 0 ; i < this.onservererrorpool.length ; i++){
                    this.onservererrorpool[i].func.status = this.status;
                    this.onservererrorpool[i].func.response = this.responseText;
                    this.onservererrorpool[i].func.xmlResponse = this.responseXML;
                    this.onservererrorpool[i].func.jsonResponse = jsonResponse;
                    if(this.onservererrorpool[i].call === true){
                        this.onservererrorpool[i].func();
                    }
                }
            }
            else if(this.readyState === 4){
                this.log('Status: '+this.status,'info');
            }
        },
        writable:false,
        enumerable: false
    });
    /**
     * A utility function used to show warning in the console about the existance 
     * of events pool.
     * @param {String} p_name The name of the pool.
     * @returns {undefined}
     */
    function noSuchPool(p_name){
        console.warn('No such bool: '+p_name);
        var pools = '';
        for(var x = 0 ; x < AJAX.CALLBACK_POOLS.length ; x++){
            if(x === AJAX.CALLBACK_POOLS.length - 1){
               pools += ' or '+ AJAX.CALLBACK_POOLS[x];
            }
            else{
                if(x === AJAX.CALLBACK_POOLS.length - 2){
                   pools += AJAX.CALLBACK_POOLS[x]; 
                }
                else{
                    pools += AJAX.CALLBACK_POOLS[x]+', ';
                }
            }
        }
        console.info('Pool name must be one of the following: '+pools);
    }
    Object.defineProperties(this,{
        isEnabled:{
            /**
            * Checks if AJAX is enabled or disabled.
            * @returns {Boolean} True if enabled and false if disabled.
            */
            value:function(){
                return this.enabled;
            },
            writable:false,
            enumerable: true
        },
        log:{
            /**
             * Shows a message in the browser's console.
             * @param {String} message The message to display.
             * @param {String} type The type of the message. It can be 'info',  
             * 'error' or 'warning'. 
             * @param {boolean} force If set to true, the message will be shown 
             * even if the logging is disabled.
             */
            value:function(message,type='',force=false){
                if(this['enable-log'] === true || force === true){
                    if(type==='info'){
                        console.info(message);
                    }
                    else if(type==='warning'){
                        console.warn(message);
                    }
                    else if(type==='error'){
                        console.error(message);
                    }
                    else{
                        console.log(message);
                    }
                }
            },
            writable:false,
            enumerable: true
        },
        setResponse:{
            /**
            * Sets the value of the property serverResponse. Do not call this function 
            * manually.
            * @param {String} response
            * @returns {undefined}
            */
            value:function(response){
                this.serverResponse = response;
                this.log('AJAX.setResponse: Response updated.','info');
            },
            writable:false,
            enumerable: true
        },
        getServerResponse:{
            /**
            * Return the value of the property serverResponse. Call this function after 
            * any complete AJAX request to get response load in case there is a load.
            * @returns {String}
            */
            value:function(){
                return this.serverResponse;
            },
            writable:false,
            enumerable: true
        },
        responseAsJSON:{
            /**
            * Return a JSON representation of response payload in case it can be convirted 
            * into JSON object. Else, in case the payload cannot be convirted, it returns 
            * undefined.
            * @returns {Object|undefined}
            */
            value:function(){
                try{
                    return JSON.parse(this.getServerResponse());
                }
                catch(e){
                    this.log('AJAX.responseAsJSON: Unable to convert server response to JSON object!','warning',true);
                }
                return undefined;
            },
            writable:false,
            enumerable: true
        },
        setOnServerError:{
            /**
            * Append a function to the pool of functions that will be called in case of 
            * server error (code 5xx). 
            * @param {Function} callback A function to call on server error. If this 
            * @param {Boolean} call If true, the method will be called. Else if false,
            * the method will be not called.
            * @returns {undefined|Number} Returns an ID for the function. If not added, 
            * the method will return undefined.
            */
            value:function(callback,call=true){
                if(typeof callback === 'function'){
                    var id = this.onservererrorpool[this.onservererrorpool.length - 1]['id'] + 1; 
                    this.onservererrorpool.push({'id':id,'call':call,'func':callback});
                    this.log('AJAX.setOnServerError: New callback added [id = '+id+' , call = '+call+'].','info');
                    return id;
                }
                else{
                    this.log('AJAX.setOnServerError: Provided parameter is not a function.','warning');
                }
                return undefined;
            },
            writable:false,
            enumerable: true
        },
        removeCall:{
            /**
            * Removes a callback function from a specific pool given its ID.
            * @param {String} pool_name The name of the pool. It should be one of the 
            * values in the array AJAX.CALLBACK_POOLS.
            * @param {Number} id The ID of the callback function.
            * @returns {undefined}
            */
            value:function(pool_name,id){
                if(pool_name !== undefined && pool_name !== null){
                    if(typeof pool_name === 'string'){
                        pool_name = pool_name.toLowerCase();
                        if(AJAX.CALLBACK_POOLS.indexOf(pool_name) !== -1){
                            pool_name = 'on'+pool_name+'pool';
                            for(var x = 0 ; x < this[pool_name].length ; x++){
                                if(this[pool_name][x]['id'] === id){
                                    return this[pool_name].pop(this[pool_name][x]);
                                }
                            }
                            this.log('AJAX.removeCall: No callback was found with ID = '+id+' in the pool \''+pool_name+'\'','error');
                        }
                        else{
                            noSuchPool(pool_name);
                        }
                    }
                    else{
                        this.log('AJAX.removeCall: Invalid pool name type. Pool name must be string.','error');
                    }
                }
                else{
                    noSuchPool(pool_name);
                }
            },
            writable:false,
            enumerable: true
        },
        disableCallExcept:{
            /**
            * Disable all callback functions except the one that its ID is given.
            * @param {String} pool_name The name of the pool. It should be a value from 
            * the array AJAX.CALLBACK_POOLS.
            * @param {Number} id The ID of the function that was provided when the function 
            * was added to the pool. If the ID does not exist, All callbacks will be disabled.
            * @returns {undefined}
            */
            value:function(pool_name,id){
                if(pool_name !== undefined && pool_name !== null){
                    if(typeof pool_name === 'string'){
                        pool_name = pool_name.toLowerCase();
                        if(AJAX.CALLBACK_POOLS.indexOf(pool_name) !== -1){
                            pool_name = 'on'+pool_name+'pool';
                            for(var x = 0 ; x < this[pool_name].length ; x++){
                                //first two IDs are reserved. do not disable.
                                if(this[pool_name][x]['id'] !== id && this[pool_name][x]['id'] > 1){
                                    this[pool_name][x]['call'] = false;
                                }
                                else{
                                    this[pool_name][x]['call'] = true;
                                }
                            }
                            return;
                        }
                        else{
                            noSuchPool(pool_name);
                        }
                    }
                    else{
                        this.log('AJAX.disableCallExcept: Invalid pool name type. Pool name must be string.','error');
                    }
                }
                else{
                    noSuchPool(pool_name);
                }
            },
            writable:false,
            enumerable: true
        },
        setCallEnabled:{
            /**
            * Enable or disable a callback on specific pool.
            * @param {String} pool_name The name of the pool. It must be one of the 
            * values in the aray AJAX.CALLBACK_POOLS.
            * @param {Number} id The ID of the callback. It is given when the callback 
            * was added.
            * @param {Boolean} call If set to true, the function will be called. Else 
            * if it is set to false, it will be not called.
            * @returns {undefined}
            */
            value:function(pool_name,id,call=true){
                if(pool_name !== undefined && pool_name !== null){
                    if(typeof pool_name === 'string'){
                        pool_name = pool_name.toLowerCase();
                        if(AJAX.CALLBACK_POOLS.indexOf(pool_name) !== -1){
                            pool_name = 'on'+pool_name+'pool';
                            for(var x = 0 ; x < this[pool_name].length ; x++){
                                if(this[pool_name][x]['id'] === id){
                                    this[pool_name][x]['call'] = call;
                                    return;
                                }
                            }
                            this.log('AJAX.setCallEnabled: No callback was found with ID = '+id+' in the pool \''+pool_name+'\'','warning');
                        }
                        else{
                            noSuchPool(pool_name);
                        }
                    }
                    else{
                        this.log('AJAX.setCallEnabled: Invalid pool name type. Pool name must be string.','error');
                    }
                }
                else{
                    noSuchPool(pool_name);
                }
            },
            writable:false,
            enumerable: true
        },
        getCallBack:{
            /**
            * Returns an object that contains the information of a callback function. 
            * @param {type} pool_name The name of the pool. It must be in the array 
            * AJAX.CALLBACK_POOLS.
            * @param {Number} id The ID of the callback.
            * @returns {Object|undefined} Returns an object that contains the 
            * information of the callback. If it is not found, or the pool name is invalid, 
            * the method will show a warning in the console and returns undefined.
            */
            value:function(pool_name='',id){
                if(pool_name !== undefined && pool_name !== null){
                    if(typeof pool_name === 'string'){
                        pool_name = pool_name.toLowerCase();
                        if(AJAX.CALLBACK_POOLS.indexOf(pool_name) !== -1){
                            pool_name = 'on'+pool_name+'pool';
                            for(var x = 0 ; x < this[pool_name].length ; x++){
                                if(this[pool_name][x]['id'] === id){
                                    return this[pool_name][x];
                                }
                            }
                            this.log('AJAX.getCallBack: No callback was found with ID = '+id+' in the pool \''+pool_name+'\'','warning');
                        }
                        else{
                            noSuchPool(pool_name);
                        }
                    }
                    else{
                        this.log('AJAX.getCallBack: Invalid pool name type. Pool name must be string.','error');
                    }
                }
                else{
                    noSuchPool(pool_name);
                }
            },
            writable:false,
            enumerable: true
        },
        setOnClientError:{
            /**
            * Append a function to the pool of functions that will be called in case of 
            * client error (code 4xx). 
            * @param {Boolean} call If true, the method will be called. Else if i i false,
            * the method will be not called.
            * @param {Function} callback A function to call on client error.
            * @returns {undefined|Number} Returns an ID for the function. If not added, 
            * the method will return undefined.
            */
            value:function(callback,call=true){
                if(typeof callback === 'function'){
                    var id = this.onclienterrorpool[this.onclienterrorpool.length - 1]['id'] + 1; 
                    this.onclienterrorpool.push({'id':id,'call':call,'func':callback});
                    this.log('AJAX.setOnClientError: New callback added [id = '+id+' , call = '+call+'].','info');
                    return id;
                }
                else{
                    this.log('AJAX.setOnClientError: Provided parameter is not a function.','error');
                }
            },
            writable:false,
            enumerable: true
        },
        setOnSuccess:{
            /**
            * Append a function to the pool of functions that will be called in case of 
            * successfull request (code 2xx). 
            * @param {Boolean} call If true, the method will be called. Else if i i false,
            * the method will be not called.
            * @param {Function} callback A function to call on success.
            * @returns {undefined|Number} Returns an ID for the function. If not added, 
            * the method will return undefined.
            */
            value:function(callback,call=true){
                if(typeof callback === 'function'){
                    var id = this.onsuccesspool[this.onsuccesspool.length - 1]['id'] + 1; 
                    this.onsuccesspool.push({'id':id,'call':call,'func':callback});
                    this.log('AJAX.setOnSuccess: New callback added [id = '+id+' , call = '+call+'].','info');
                    return id;
                }
                else{
                    this.log('AJAX.setOnSuccess: Provided parameter is not a function.','error');
                }
            },
            writable:false,
            enumerable: true
        },
        setOnDisconnected:{
            /**
            * Append a function to the pool of functions that will be called in case of 
            * internec connection is lost (code 0). 
            * @param {Boolean} call If true, the method will be called. Else if false,
            * the method will be not called.
            * @param {Function} callback A function to call on lost connection event.
            * @returns {undefined|Number} Returns an ID for the function. If not added, 
            * the method will return undefined.
            */
            value:function(callback,call=true){
                if(typeof callback === 'function'){
                    var id = this.onconnectionlostpool[this.onconnectionlostpool.length - 1]['id'] + 1; 
                    this.onconnectionlostpool.push({'id':id,'call':call,'func':callback});
                    this.log('AJAX.setOnDisconnected: New callback added [id = '+id+' , call = '+call+'].','info');
                    return id;
                }
                else{
                    this.log('AJAX.setOnDisconnected: Provided parameter is not a function.','error');
                }
            },
            writable:false,
            enumerable: true
        },
        setReqMethod:{
            /**
            * Sets the request method.
            * @param {String} method get, post or delete. If the request method is not 
            * supported, A warning will be shown in the console and default (GET) will 
            * be used.
            * @returns {undefined}
            */
            value:function(method){
                if(method !== undefined && method !== null){
                    method = method.toUpperCase();
                    if(method === 'GET' || method === 'POST' || method === 'DELETE'){
                        this.method = method;
                        this.log('AJAX.setReqMethod: Request method is set to '+method+'.','info');
                    }
                    else{
                        this.log('AJAX.setReqMethod: Null, undefined or unsupported method. GET is set as default.','warning',true);
                        this.method = 'GET';
                    }
                }
                else{
                    this.log('AJAX.setReqMethod: Null, undefined or unsupported method. GET is set as default.','warning',true);
                    this.method = 'GET';
                }
            },
            writable:false,
            enumerable: true
        },
        getReqMethod:{
            /**
            * Returns request method.
            * @returns {String}
            */
            value:function(){
                return this.method;
            },
            writable:false,
            enumerable: true
        },
        setURL:{
            /**
            * Sets AJAX request URL (or URI)
            * @param {String} url
            * @returns {undefined}
            */
            value:function(url){
                this.url = url;
                this.log('AJAX.setURL: URL is set to \''+url+'\'.','info');
            },
            writable:false,
            enumerable: true
        },
        getURL:{
            /**
            * Returns request URL.
            * @returns {String}
            */
            value:function(){
                return this.url;
            },
            writable:false,
            enumerable: true
        },
        setParams:{
            /**
            * Sets request payload that will be send with it.
            * @param {String} params
            * @returns {undefined}
            */
            value:function(params){
                this.params = params;
                this.log('AJAX.setParams: Parameters is set to \''+params+'\'.','info');
            },
            writable:false,
            enumerable: true
        },
        getParams:{
            /**
            * Returns request payload.
            * @returns {String}
            */
            value:function(){
                return this.params;
            },
            writable:false,
            enumerable: true
        },
        send:{
            /**
            * Send AJAX request to the server.
            * @returns {Boolean} True in case of the status of AJAX request is open. 
            * else, it will return false.
            */
            value:function(){
                this.log('AJAX.send: Sending AJAX request.','info');
                if(this.isEnabled()){
                    var method = this.getReqMethod();
                    var params = this.getParams();
                    var url = this.getURL();
                    this.log('AJAX.send: Params: '+params,'info');
                    this.log('AJAX.send: Request Method: '+method,'info');
                    this.log('AJAX.send: URL: '+url,'info');
                    this.xhr.log = this.log;
                    this.xhr.onreadystatechange = this.onreadystatechange;
                    this.xhr.onload = this.onload;
                    this.xhr.onprogress = this.onprogress;
                    this.xhr.onsuccesspool = this.onsuccesspool;
                    this.xhr.onservererrorpool = this.onservererrorpool;
                    this.xhr.onclienterrorpool = this.onclienterrorpool;
                    this.xhr.onconnectionlostpool = this.onconnectionlostpool;
                    this.xhr['enable-log'] = this['enable-log'];
                    if(method === 'GET' || method === 'DELETE'){
                        if(params !== undefined && params !== null && params !== ''){
                            this.xhr.open(method,url+'?'+params);
                        }
                        else{
                            this.xhr.open(method,url);
                        }
                        this.xhr.send();
                        return true;
                    }
                    else if(method === 'POST'){
                        this.xhr.open(method,url);
                        if(this.params.toString() !== '[object FormData]'){
                            this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            this.log('AJAX.send: Setting header \'Content-Type\' to \'application/x-www-form-urlencoded\'.','info');
                        }
                        this.xhr.send(params);
                        return true;
                    }
                    else{
                        this.log('AJAX.send: Method not supported: '+method,'info',true);
                    }
                }
                else{
                    this.log('AJAX.send: AJAX is disabled.','info',true);
                }
                return false;
            },
            writable:false,
            enumerable: true
        },
        setEnabled:{
            /**
            * Enable or disable AJAX.
            * @param {Boolean} boolean True to enable AJAX. False to disable. If 
            * other value is given, AJAX will be enabled.
            * @returns {undefined}
            */
            value:function(boolean){
                if(boolean === true){
                    this.enabled = true;
                    this.log('AJAX.setEnabled: AJAX is enabled.','info');
                }
                else if(boolean === false){
                    this.enabled = false;
                    this.log('AJAX.setEnabled: AJAX is disabled.','info');
                }
                else{
                    this.enabled = true;
                    this.log('AJAX.setEnabled: AJAX is enabled.','info');
                }
            },
            writable:false,
            enumerable: true
        },
        xhr:{
            /**
            * The XMLHttpRequest object that is used to send AJAX.
            */
            value:AJAX.createXhr(),
            writable:false,
            enumerable: true
        }
    });
    //configuration 
    if(this.xhr === false || this.xhr === undefined || this.xhr === null){
        this.log('AJAX: Unable to creeate xhr object! Browser does not support it.','error',true);
        return;
    }
    var instance = this;
    var a = function(){
        instance.setResponse(instance.xhr.responseText);
    };
    this['enable-log'] = config['enable-log'];
    if(this['enable-log'] === true){
        this.log('AJAX: Logging mode is enabled.');
    }
    this.setOnSuccess(a);
    this.setOnServerError(a);
    this.setOnClientError(a);
    this.setReqMethod(config.method);
    this.setURL(config.url);
    this.setEnabled(config.enabled);
}