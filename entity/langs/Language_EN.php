<?php
if(!defined('ROOT_DIR')){
    http_response_code(403);
    die('{"message":"Forbidden"}');
}
/**
 * English language definition.
 */
$Language = new Language('ltr', 'en', array(
    'general',
    'general/http-codes/200',
    'general/http-codes/201',
    'general/http-codes/400',
    'general/http-codes/403',
    'general/http-codes/404',
    'general/http-codes/405',
    'general/http-codes/408',
    'general/http-codes/415',
    'general/http-codes/500',
    'general/http-codes/501',
    'general/http-codes/505',
    'pages/setup/setup-steps',
    'pages/setup/welcome/help',
    'pages/setup/database-setup/labels',
    'pages/setup/database-setup/placeholders',
    'pages/setup/database-setup/errors',
    'pages/setup/database-setup/help',
    'pages/setup/email-account/labels',
    'pages/setup/email-account/placeholders',
    'pages/setup/email-account/errors',
    'pages/setup/email-account/help',
    'pages/setup/admin-account/labels',
    'pages/setup/admin-account/placeholders',
    'pages/setup/admin-account/errors',
    'pages/setup/admin-account/help',
    'pages/setup/website-config/labels',
    'pages/setup/website-config/placeholders',
    'pages/setup/website-config/errors',
    'pages/setup/website-config/help',
    'pages/home',
    'pages/login/labels',
    'pages/login/placeholders',
    'pages/login/errors',
    'pages/login/actions',
    'pages/activate-account/labels',
    'pages/activate-account/placeholders',
    'pages/activate-account/actions',
    'pages/activate-account/errors',
    'pages/activate-account/status',
    'pages/new-password/labels',
    'pages/new-password/placeholders',
    'pages/new-password/actions',
    'pages/new-password/errors',
    'pages/new-password/status',
    'email/'
));
$Language->setMultiple('general/http-codes/200', array(
    'code'=>200,
    'type'=>'OK',
    'message'=>''
));
$Language->setMultiple('general/http-codes/201', array(
    'code'=>201,
    'type'=>'Created',
    'message'=>''
));
$Language->setMultiple('general/http-codes/400', array(
    'code'=>400,
    'type'=>'Bad Request',
    'message'=>'Server could not understand the request due to invalid syntax.'
));
$Language->setMultiple('general/http-codes/403', array(
    'code'=>403,
    'type'=>'Forbidden',
    'message'=>'You are not allowed to view the content of the requested resource.'
));
$Language->setMultiple('general/http-codes/404', array(
    'code'=>404,
    'type'=>'Not Found',
    'message'=>'The requested resource cannot be found.'
));
$Language->setMultiple('general/http-codes/405', array(
    'code'=>405,
    'type'=>'Method Not Allowed',
    'message'=>'The method that is used to get the resource is not allowed.'
));
$Language->setMultiple('general/http-codes/408', array(
    'code'=>408,
    'type'=>'Request Timeout',
    'message'=>''
));
$Language->setMultiple('general/http-codes/415', array(
    'code'=>415,
    'type'=>'Unsupported Media Type',
    'message'=>'The payload format is not supported by the server.'
));
$Language->setMultiple('general/http-codes/500', array(
    'code'=>500,
    'type'=>'Server Error',
    'message'=>'Internal server error.'
));
$Language->setMultiple('general/http-codes/501', array(
    'code'=>501,
    'type'=>'Not Implemented',
    'message'=>'The request method is not supported.'
));
$Language->setMultiple('general/http-codes/505', array(
    'code'=>505,
    'type'=>'HTTP Version Not Supported',
    'message'=>'The HTTP version used in the request is not supported by the server.'
));
$Language->set('general', 'wait', 'Please wait a moment...');
$Language->set('general', 'loading', 'Loading...');
$Language->set('general', 'checking', 'Checking...');
$Language->set('general', 'validating', 'Validating...');
$Language->set('general', 'loaded', 'Loaded.');
$Language->set('general', 'save', 'Save');
$Language->set('general', 'saving', 'Saving...');
$Language->set('general', 'saved', 'Saved');
$Language->set('general', 'remove', 'Remove');
$Language->set('general', 'removing', 'Removing...');
$Language->set('general', 'removed', 'Removed.');
$Language->set('general', 'delete', 'Delete');
$Language->set('general', 'deleting', 'Deleting...');
$Language->set('general', 'deleted', 'Deleted.');
$Language->set('general', 'connected', 'Connected.');
$Language->set('general', 'disconnected', 'Disconnected. Please check your interned connection.');
$Language->set('general', 'next', 'Next');
$Language->set('general', 'previous', 'Previous');
$Language->set('general', 'skip', 'Skip');
$Language->set('general', 'finish', 'Finish');
$Language->set('general', 'server-err', 'Unkouwn Server Error!');
$Language->setMultiple('pages/setup/setup-steps', array(
    'welcome'=>'Welcome',
    'database-setup'=>'Database Setup',
    'admin-account'=>'Admin Account',
    'email-account'=>'Email Setup',
    'website-config'=>'Website Configuration',
));
$Language->setMultiple('pages/setup/welcome/help', array(
    'h-1'=>'Welcome to <b>LisksCode</b> framework setup. Since This is the '
    . 'first time you are using the framework, we will need from you '
    . 'to give us few moments to setup the basic settings. You can change '
    . 'your settings later if you want.',
    'h-2'=>'The first thing that we need from you is to provide the '
    . 'us with MySQL database connection information. Next, you will have to enter '
    . 'the information of SMTP Email account that will be used to send system '
    . 'notifications to users and admin. After that, you '
    . 'will have to create an admin account. The final step is to configure '
    . 'some of the basic website settings.',
    'h-3'=>'Before you continue with the setup, please make sure that you '
    . 'have the following things ready:',
    'h-4'=>'MySQL Database Account Information.',
    'h-5'=>'SMTP Email Account Information.',
));
$Language->setMultiple('pages/setup/welcome', array(
    'title'=>'Welcome',
    'description'=>'Welcome to setup.'
));
$Language->setMultiple('pages/setup/database-setup/labels', array(
    'username'=>'Username:',
    'host'=>'Host Address:',
    'password'=>'Password:',
    'database-name'=>'Database Name:',
    'check-connection'=>'Check Connection',
    'connected'=>'Connection Established Successfully!'
));
$Language->setMultiple('pages/setup/database-setup/placeholders', array(
    'username'=>'The username of database user.',
    'host'=>'localhost or some URL or IP address.',
    'password'=>'The password of database user.',
    'database-name'=>'The name of the database.',
));
$Language->setMultiple('pages/setup/database-setup/errors', array(
    2002=>'Check that your host name is correct and that your host has MySql Server installed.',
    1045=>'Check that your database username and password are correct.',
    1044=>'You don\'t have permissions to access the given database.',
    1049=>'Check that the database name is correct.',
    10000=>'The given database is not empty. Selected database must have no tables.'
));
$Language->setMultiple('pages/setup/database-setup/help', array(
    'h-1'=>'In this step, you are required to privide your MySQL database information. '
    . 'First of all, We need from you to give us your database host. The database host can '
    . 'be a URL or an IP address. If your database is in the same server as the website, use \'localhost\'.',
    'h-2'=>'The second thing we need is a user account that is used to access the database. '
    . 'The account must have all prevelages over the database (select, insert, update, delete etc...). ',
    'h-3'=>'The last thing that we need is the name of database instance that will be used.'
));
$Language->setMultiple('pages/setup/database-setup', array(
    'title'=>'Database Setup',
    'description'=>'Setting up your database connection.'
));
$Language->setMultiple('pages/setup/email-account/labels', array(
    'name'=>'Account Name:',
    'server-address'=>'Server Address:',
    'email-address'=>'Email Address:',
    'username'=>'Username',
    'password'=>'Password:',
    'check-connection'=>'Check Connection',
    'connected'=>'Connection Established Successfully!',
    'port'=>'Server Port:'
));
$Language->setMultiple('pages/setup/email-account/placeholders', array(
    'name'=>'Something like: \'Programming Academia Team\'',
    'server-address'=>'mail.example.com',
    'email-address'=>'ma_address@example.com',
    'username'=>'Server username.',
    'password'=>'Login password.',
    'port'=>'25'
));
$Language->setMultiple('pages/setup/email-account/errors', array(
    'inv_mail_host_or_port'=>'Incorrect server address or port.',
    'inv_username_or_pass'=>'Incorrect username or password.'
));
$Language->setMultiple('pages/setup/email-account/help', array(
    'h-1'=>'In this step, we need from you to give us the information of '
    . 'the email account hat we are going to use in order to send notifications. '
    . 'We need from you to give us SMTP server information (Port and address) in addition to '
    . 'SMTP account information.',
    'h-2'=>'If you don\' have the information, you can skip this step.'
));
$Language->setMultiple('pages/setup/email-account', array(
    'title'=>'SMTP Account',
    'description'=>'SMTP account information.'
));
$Language->setMultiple('pages/setup/admin-account/labels', array(
    'username'=>'Username:',
    'password'=>'Password:',
    'conf-password'=>'Confirm Password:',
    'email-address'=>'Email Address:',
    'run-setup'=>'Run Setup',
));
$Language->setMultiple('pages/setup/admin-account/placeholders', array(
    'username'=>'Type in a username (Only English Chraracters and numbers)',
    'password'=>'Choose a strong password.',
    'conf-password'=>'Type in your password again.',
    'email-address'=>'Type in your email address.'
));
$Language->setMultiple('pages/setup/admin-account/errors', array(
    'password-missmatch'=>'The typed passwords does not match.',
    'inv-email'=>'The given email address is invalid.'
));
$Language->setMultiple('pages/setup/admin-account/help', array(
    'h-1'=>'In this step, you have to create one admin account. '
    . 'This account will have all the control over every functionality in '
    . 'the system. We need from you to choose a nice username, a password and '
    . 'an email address to comunicate with you in case of important things. '
    . 'Please do not forget your password. Because once this happens, you will '
    . 'loose access to the system for ever.',
    'h-2'=>'<b>Note that once you finish this step, there is no going back.<b>'
));
$Language->setMultiple('pages/setup/admin-account', array(
    'title'=>'Adminstrator Account',
    'description'=>'Setup adminstrator account.'
));
$Language->setMultiple('pages/setup/website-config/labels', array(
    'site-name'=>'Website Name:',
    'site-description'=>'Website Description:',
    'title-sep'=>'',
    'home-page'=>'',
    'selected-theme'=>''
));
$Language->setMultiple('pages/setup/website-config/placeholders', array(
    'site-name'=>'Choose a name that reflects site content.',
    'site-description'=>'Give a short description for your website.'
));
$Language->setMultiple('pages/setup/website-config/errors', array(
    
));
$Language->setMultiple('pages/setup/website-config/help', array(
    'h-1'=>'This is the last step in the setup process. What we need from '
    . 'you is to give your website a name. This name will usually appear '
    . 'along side the name of the page that you are browsing. It can be seen '
    . 'in browser\'s top bar.',
    'h-2'=>'The second thing that we need is a short description for your website. '
    . 'The given description will usually appear by default in the pages that does not '
    . 'have descripion.'
));
$Language->setMultiple('pages/setup/admin-account', array(
    'title'=>'Website Configuration',
    'description'=>'Final step in setup.'
));
$Language->setMultiple('pages/login/labels', array(
    'main'=>'Login to The System',
    'username' => 'Username or email:',
    'password' => 'Password:',
    'keep-me-logged'=>'Keep me logged in for one week.',
));
$Language->setMultiple('pages/login/placeholders', array(
    'username' => 'Enter Your Username or Email',
    'password' => 'Enter Your Password Here',
));
$Language->setMultiple('pages/login/errors', array(
    'something-wrong'=>'Something went wrong. Try again in few moments. Sorry about that 😲',
    'incorrect-login-params' => 'Inccorrect username, email or password!',
    'err-missing-pass' => 'Missing Password!',
    'err-missing-username' => 'Missing Username!'
));
$Language->setMultiple('pages/login/actions', array(
    'login'=>'Login',
    'fogot-pass'=>'Did you Forgot your Password?'
));
$Language->setMultiple('pages/home', array(
    'title'=>'Home Page',
    'description'=>'This is my home page.'
));
$Language->setMultiple('pages/login', array(
    'title'=>'Login',
    'description'=>'Login to the system.'
));


$Language->setMultiple('pages/activate-account', array(
    'title'=>'Account Activation',
    'description'=>'A page to activate user account.'
));
$Language->setMultiple('pages/activate-account/labels', array(
    'main'=>'Activate Your Account',
    'activation-token'=>'Activation Token:'
));
$Language->setMultiple('pages/activate-account/status', array(
    'activating'=>'Activating your account. Please wait a moment...',
    'activated'=>'Your account was activated successfully.'
));
$Language->setMultiple('pages/activate-account/placeholders', array(
    'activation-token'=>'Type in or paste your activation token here.'
));
$Language->setMultiple('pages/activate-account/actions', array(
    'activate'=>'Activate Account'
));
$Language->setMultiple('pages/activate-account/errors', array(
    'inncorect-token'=>'Inncorrect Activation token!'
));

$Language->setMultiple('pages/new-password', array(
    'title'=>'Create New Password',
    'description'=>'A page to create new password.'
));
$Language->setMultiple('pages/new-password/labels', array(
    'main'=>'Creating New Password',
    'email'=>'Email Address:',
    'password'=>'New Password:',
    'conf-pass'=>'Confirm Password:',
));
$Language->setMultiple('pages/new-password/status', array(
    'resetting'=>'Updating your password. Please wait a moment...',
    'resetted'=>'Your account password was updated successfully.'
));
$Language->setMultiple('pages/new-password/placeholders', array(
    'email'=>'Type in your email address here.',
    'password'=>'Type in your new password here.',
    'conf-pass'=>'Type in the same new password here.',
));
$Language->setMultiple('pages/new-password/actions', array(
    'reset'=>'Reset Password'
));
$Language->setMultiple('pages/new-password/errors', array(
    'password-missmatch'=>'The given two passwords do not match.',
    'inv-email'=>'The given email address is invalid.',
));