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
/**
 * An object that contains API links.
 * @type type
 */
function APIS(){
    
}
Object.defineProperties(APIS,{
    base:{
        /**
         * The Base APIs URL. Modify as needed.
         */
        value:'http://localhost/generic-php',
        enumerable:true
    }
});
Object.defineProperties(APIS,{
    AuthAPI:{
        value:{},
        enumerable:true
    },
    UserAPIs:{
        value:{},
        enumerable:true
    },
    FileAPIs:{
        value:{},
        enumerable:true
    },
    SysAPIs:{
        value:{},
        enumerable:true
    },
    NumsAPIs:{
        value:{},
        enumerable:true
    },
    PasswordAPIs:{
        value:{},
        enumerable:true
    },
    WebsiteAPIs:{
        value:{},
        enumerable:true
    }
});
Object.defineProperties(APIS.AuthAPI,{
    link:{
        value: APIS.base+'/AuthAPI',
        enumerable:true
    }
});
Object.defineProperties(APIS.UserAPIs,{
    link:{
        value: APIS.base+'/UserAPIs',
        enumerable:true
    }
});
Object.defineProperties(APIS.FileAPIs,{
    link:{
        value: APIS.base+'/FileAPIs',
        enumerable:true
    }
});
Object.defineProperties(APIS.NumsAPIs,{
    link:{
        value: APIS.base+'/NumsAPIs',
        enumerable:true
    }
});
Object.defineProperties(APIS.PasswordAPIs,{
    link:{
        value: APIS.base+'/PasswordAPIs',
        enumerable:true
    }
});
Object.defineProperties(APIS.SysAPIs,{
    link:{
        value: APIS.base+'/SysAPIs',
        enumerable:true
    }
});
Object.defineProperties(APIS.WebsiteAPIs,{
    link:{
        value: APIS.base+'/WebsiteAPIs',
        enumerable:true
    }
});
