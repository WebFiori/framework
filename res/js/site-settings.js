/* 
 * The MIT License
 *
 * Copyright 2018 Ibrahim.
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
/* global APIS */

/**
 * 
 * @param {Object} settings An object that contains website settings. 
 * The structure of the object is as follows:
 * <pre>
 * {<br/>
 * &nbsp;&nbsp;name:''<br/>
 * &nbsp;&nbsp;description:''<br/>
 * &nbsp;&nbsp;title-sep:''<br/>
 * &nbsp;&nbsp;home-page:''<br/>
 * &nbsp;&nbsp;site-theme:''<br/>
 * &nbsp;&nbsp;callbacks:{<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onsuccess:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onclienterr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;onservererr:[]<br/>
 * &nbsp;&nbsp;&nbsp;&nbsp;ondisconnected:[]<br/>
 * &nbsp;&nbsp;}<br/>
 * }
 * </pre>
 * @returns {undefined}
 */
function updateSiteSettings(settings={
    name:'',
    description:'',
    'title-sep':'',
    'home-page':'',
    'site-theme':'',
    onsucess:[],
    onclienterr:[],
    onservererr:[],
    ondisconnected:[]
}){
    var ajax = new AJAX({
        method:'post',
        url:APIS.SysAPIs.link
    });
    var form = new FormData();
    form.append('action','update-site-info');
    if(settings['name'] !== undefined){
        form.append('site-name',settings['name']);
    }
    if(settings['description'] !== undefined){
        form.append('site-description',settings['description']);
    }
    if(settings['title-sep'] !== undefined){
        form.append('title-sep',settings['title-sep']);
    }
    if(settings['home-page'] !== undefined){
        form.append('home-page',settings['home-page']);
    }
    if(settings['site-theme'] !== undefined){
        form.append('site-theme',settings['site-theme']);
    }
    ajax.setParams(form);
    
    if(Array.isArray(settings['onsucess'])){
        for(var x = 0 ; x < settings['onsucess'].length ; x++){
            var call = settings['onsucess'][x];
            if(typeof call === 'function'){
                ajax.setOnSuccess(call);
            }
        }
    }
    
    if(Array.isArray(settings['onclienterr'])){
        for(var x = 0 ; x < settings['onclienterr'].length ; x++){
            var call = settings['onclienterr'][x];
            if(typeof call === 'function'){
                ajax.setOnClientError(call);
            }
        }
    }
    
    if(Array.isArray(settings['onservererr'])){
        for(var x = 0 ; x < settings['onservererr'].length ; x++){
            var call = settings['onservererr'][x];
            if(typeof call === 'function'){
                ajax.setOnServerError(call);
            }
        }
    }
    
    if(Array.isArray(settings['ondisconnected'])){
        for(var x = 0 ; x < settings['ondisconnected'].length ; x++){
            var call = settings['ondisconnected'][x];
            if(typeof call === 'function'){
                ajax.setOnDisconnected(call);
            }
        }
    }
    ajax.send();
}

