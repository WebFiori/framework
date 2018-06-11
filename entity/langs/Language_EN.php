<?php
$Language = new Language('ltr', 'en', array(
    'general',
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
    'email/'
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
    'h-1'=>'Welcome to application setup. Since this is your first '
    . 'time using the app, you must setup few things.',
    'h-2'=>'The first thing that we need from you is to provide the '
    . 'application with database connection information. Next, you will have to enter '
    . 'the information of SMTP Email account that will be used to send system '
    . 'notifications to users and admin. After that, you '
    . 'will have to create an admin account. The final step is to configure '
    . 'some of the basic website settings.',
    'h-3'=>'Before you continue with the setup, please make sure that you '
    . 'have the following things ready:',
    'h-4'=>'MySQL Database Account Information.',
    'h-5'=>'SMTP Email Account Information.',
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
/**
 * A language file that represents English language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
//const LANGUAGE = array(
//    'dir'=>'ltr',
//    'general'=>array(
//        'wait'=>'Please wait a moment 🙂',
//        'loading'=>'Loading...',
//        'saving'=>'Saving...',
//        'next'=>'Next',
//        'saved'=>'Saved!',
//        'prev'=>'Previous',
//        'skip'=>'Skip',
//        'connected'=>'Connected!',
//        'disconnected'=>'Please check that you are connected to the internet.',
//        'save'=>'Save Changes',
//        'finish'=>'Finish'
//    ),
//    'pages'=>array(
//        'activate-account'=>array(
//            'title'=>'Account Activation',
//            'description'=>'A page to activate user account.',
//            'success'=>'Account Activated!',
//            'labels'=>array(
//                'main'=>'Activate Your Account',
//                'activation-token'=>'Activation Token:'
//            ),
//            'placeholders'=>array(
//                'activation-token'=>'Enter your activation token here.'
//            ),
//            'actions'=>array(
//                'activate'=>'Activate'
//            ),
//            'errors'=>array(
//                'inncorect-token'=>'Inncorrect Activation token!'
//            )
//        ),
//        'settings'=>array(
//            'title'=>'',
//            'description'=>'',
//            'settings'=>array(
//                'website-settings'=>array(
//                    
//                ),
//                'database-settings'=>array(
//                    
//                ),
//                'smtp-settings'=>array(
//                    'labels'=>array(
//                        'name'=>'Account Name:',
//                        'server-address'=>'Server Address:',
//                        'email-address'=>'Email Address:',
//                        'username'=>'Username',
//                        'password'=>'Password:',
//                        'check-connection'=>'Check Connection',
//                        'connected'=>'Connection Established Successfully!',
//                        'port'=>'Server Port:'
//                    ),
//                    'placeholders'=>array(
//                        'name'=>'Something like: \'Programming Academia Team\'',
//                        'server-address'=>'mail.example.com',
//                        'email-address'=>'ma_address@example.com',
//                        'username'=>'Server username.',
//                        'password'=>'Login password.',
//                        'port'=>'25'
//                    ),
//                    'status'=>array(
//                        'checking-connection'=>'Validating connection info...',
//                    ),
//                    'errors'=>array(
//                        'inv_mail_host_or_port'=>'Incorrect server address or port.',
//                        'inv_username_or_pass'=>'Incorrect username or password.'
//                    )
//                )
//            )
//        ),
//        'login'=>array(
//            'description'=>'Login to the system.',
//            'title'=>'Login Page',
//            'success'=>'You are logged in.',
//            'actions'=>array(
//                'login' => 'Login',
//            ),
//            'labels'=>array(
//                'main'=>'Login to The System',
//                'username' => 'Username or email:',
//                'password' => 'Password:',
//                'keep-me-logged'=>'Keep me logged in for one week.',
//            ),
//            'placeholders'=>array(
//                'username' => 'Enter Your Username or Email',
//                'password' => 'Enter Your Password Here',
//            ),
//            'errors'=>array(
//                'something-wrong'=>'Something went wrong. Try again in few moments. Sorry about that 😲',
//                'incorrect-login-params' => 'Inccorrect username, email or password!',
//                'err-missing-pass' => 'Missing Password!',
//                'err-missing-username' => 'Missing Username!'
//            )
//        ),
//        'home'=>array(
//            'title'=>'Home Page',
//            'description'=>'This is the home page'
//        ),
//        'sys-info'=>array(
//            'title'=>'System Information',
//            'description'=>'About the System.'
//        ),
//        'profile'=>array(
//            'title'=>'My Profile',
//            'description'=>'View your own profile.',
//            'labels'=>array(
//                'username'=>'Username:',
//                'display-name'=>'Display Name:',
//                'email'=>'Email:',
//                'status'=>'Status:',
//                'reg-date'=>'Registration Date',
//                'last-login'=>'Last Login',
//                'access-level'=>'Access Level:',
//                'activation-token'=>'Activation Token:',
//                'actions'=>'Profile Actions:',
//                'update-email'=>'Update Email',
//                'update-disp-name'=>'Update Display Name',
//                'update-password'=>'Update Password'
//            )
//        ),
//        'update-disp-name'=>array(
//            'title'=>'Update Display Name',
//            'description'=>'Change User Display Name.',
//            'labels'=>array(
//                'empty-name'=>'Display name cannot be empty.',
//                'disp-name'=>'New Display Name:',
//                'update'=>'Update Display Name',
//                'updated'=>'<b style="color:green">Display Name Updated!</b>'
//            )
//        ),
//        'update-email'=>array(
//            'title'=>'Update User Email Address',
//            'description'=>'Change User Email Address.',
//            'labels'=>array(
//                'empty-email'=>'Email cannot be empty.',
//                'email'=>'New Email Address:',
//                'old-pass'=>'New Email:',
//                'update'=>'Update Email',
//                'updated'=>'<b style="color:green">Email updated!</b>'
//            )
//        ),
//        'update-pass'=>array(
//            'title'=>'Update User Password',
//            'description'=>'Change User Password.',
//            'labels'=>array(
//                'old-pass'=>'Old Password:',
//                'new-pass'=>'New Password:',
//                'conf-pass'=>'Confirm New Password:',
//                'update'=>'Update Password',
//                'pass-missmatch'=>'Confirmation password is incorrect!',
//                'empty-old-pass'=>'Old password cannot be empty.',
//                'empty-new-password'=>'New password cannot be empty.',
//                'incorrect-old-pass'=>'Incorrect old password!',
//                'updated'=>'<b style="color:green">Password updated!</b>'
//            )
//        ),
//        'view-users'=>array(
//            'title'=>'System Users',
//            'description'=>'List of System Users.',
//            'labels'=>array(
//                'username'=>'Username',
//                'disp-name'=>'Display Name',
//                'email'=>'Email',
//                'status'=>'Status',
//                'reg-date'=>'Registration Date',
//                'last-login'=>'Last Login'
//            )
//        ),
//        'register'=>array(
//            'title'=>'Create User Profile',
//            'description'=>'Creating new profile.',
//            'labels'=>array(
//                'username'=>'Username:',
//                'password'=>'Password:',
//                'conf-pass'=>'Confirm Password:',
//                'email'=>'Email:',
//                'disp-name'=>'Display Name:',
//                'reg'=>'Register',
//                'acc-lvl'=>'Access Level:'
//            ),
//            'errors'=>array(
//                'missing-acc-lvl'=>'Select Access Level!',
//                'missing-username'=>'Username cannot be empty.',
//                'missing-pass'=>'Password cannot be empty.',
//                'pass-missmatch'=>'Check your confirmed password.',
//                'missing-email'=>'Email cannot be empty.'
//            )
//        ),
//    ),
//    'aside'=>array(
//        'logout'=>'Logout',
//        'home'=>'Home Page',
//        'profile'=>'User Profile',
//        'add-user'=>'Create User',
//        'view-users'=>'View Users',
//        'sys-info'=>'System Info'
//    ),
//    'api-messages'=>array(
//        
//    )
//);