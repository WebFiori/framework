<?php

/* 
 * The MIT License
 *
 * Copyright 2018 ibrah.
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

Access::newPrivilege('SU_GROUP', 'ADD_USER');
Access::newPrivilege('SU_GROUP', 'UPDATE_USER_PASS_ALL');
Access::newPrivilege('SU_GROUP', 'UPDATE_USER_PASS_EMAIL_ALL');
Access::newPrivilege('SU_GROUP', 'UPDATE_USER_PRIVILEGES');
Access::newPrivilege('SU_GROUP', 'SUSPEND_USER');
Access::newPrivilege('SU_GROUP', 'REMOVE_USER');
Access::newGroup('TEST_GR');
Access::newPrivilege('TEST_GR', 'TEST_PR_1');
$u = new User();
Access::resolvePriviliges('ADD_USER-1;UPDATE_USER_PASS_ALL-1', $u);
Util::print_r($u->privileges());
if($u->hasPrivilege('ADD_USER')){
    Util::print_r('User can create user profile');
}
else{
    Util::print_r('User can not create user profile');
}
if($u->inGroup('SU_GROUP')){
    Util::print_r('User is in super admin group');
}
else{
    Util::print_r('User is in not super admin group');
}