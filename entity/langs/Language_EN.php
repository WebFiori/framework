<?php
/**
 * A language file that represents English language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
const LANGUAGE = array(
    'dir'=>'ltr',
    'general'=>array(
        'wait'=>'Please wait a moment 🙂',
        'loading'=>'Loading...',
        'next'=>'Next',
        'prev'=>'Previous'
    ),
    'pages'=>array(
        'setup'=>array(
            'setup-steps'=>array(
                'welcome'=>'Welcome',
                'database-setup'=>'Database Setup',
                'admin-account'=>'Admin Account',
                'website-config'=>'Website Configuration',
                'finish'=>'Finish'
            ),
            'welcome'=>array(
                'title'=>'Welcome',
                'description'=>'Fist page.',
                'help'=>array(
                    'h-1'=>'Welcome to application setup. Since this is your first '
                    . 'time using the app, you must setup few things.',
                    'h-2'=>'The first thing that we need from you is to provide the '
                    . 'application with database connection information. After that, you '
                    . 'will have to create an admin account. The final step is to configure '
                    . 'some of the basic website settings.'
                )
            ),
            'database-setup'=>array(
                'title'=>'Database Setup',
                'description'=>'',
                'help'=>''
                
            ),
            'admin-account'=>array(
                'title'=>'Creating Admin Account',
                'description'=>'',
                'help'=>''
            ),
            'website-config'=>array(
                'title'=>'Configuing Website',
                'description'=>'',
                'help'=>''
            ),
            'finish'=>array(
                'title'=>'Finished',
                'description'=>'',
                'help'=>''
            )
        ),
        'activate-account'=>array(
            'title'=>'Account Activation',
            'description'=>'A page to activate user account.',
            'success'=>'Account Activated!',
            'labels'=>array(
                'main'=>'Activate Your Account',
                'activation-token'=>'Activation Token:'
            ),
            'placeholders'=>array(
                'activation-token'=>'Enter your activation token here.'
            ),
            'actions'=>array(
                'activate'=>'Activate'
            ),
            'errors'=>array(
                'inncorect-token'=>'Inncorrect Activation token!'
            )
        ),
        'login'=>array(
            'description'=>'Login to the system.',
            'title'=>'Login Page',
            'success'=>'You are logged in.',
            'actions'=>array(
                'login' => 'Login',
            ),
            'labels'=>array(
                'main'=>'Login to The System',
                'username' => 'Username or email:',
                'password' => 'Password:',
                'keep-me-logged'=>'Keep me logged in for one week.',
            ),
            'placeholders'=>array(
                'username' => 'Enter Your Username or Email',
                'password' => 'Enter Your Password Here',
            ),
            'errors'=>array(
                'something-wrong'=>'Something went wrong. Try again in few moments. Sorry about that 😲',
                'incorrect-login-params' => 'Inccorrect username, email or password!',
                'err-missing-pass' => 'Missing Password!',
                'err-missing-username' => 'Missing Username!'
            )
        ),
        'home'=>array(
            'title'=>'Home Page',
            'description'=>'This is the home page'
        ),
        'sys-info'=>array(
            'title'=>'System Information',
            'description'=>'About the System.'
        ),
        'profile'=>array(
            'title'=>'My Profile',
            'description'=>'View your own profile.',
            'labels'=>array(
                'username'=>'Username:',
                'display-name'=>'Display Name:',
                'email'=>'Email:',
                'status'=>'Status:',
                'reg-date'=>'Registration Date',
                'last-login'=>'Last Login',
                'access-level'=>'Access Level:',
                'activation-token'=>'Activation Token:',
                'actions'=>'Profile Actions:',
                'update-email'=>'Update Email',
                'update-disp-name'=>'Update Display Name',
                'update-password'=>'Update Password'
            )
        ),
        'update-disp-name'=>array(
            'title'=>'Update Display Name',
            'description'=>'Change User Display Name.',
            'labels'=>array(
                'empty-name'=>'Display name cannot be empty.',
                'disp-name'=>'New Display Name:',
                'update'=>'Update Display Name',
                'updated'=>'<b style="color:green">Display Name Updated!</b>'
            )
        ),
        'update-email'=>array(
            'title'=>'Update User Email Address',
            'description'=>'Change User Email Address.',
            'labels'=>array(
                'empty-email'=>'Email cannot be empty.',
                'email'=>'New Email Address:',
                'old-pass'=>'New Email:',
                'update'=>'Update Email',
                'updated'=>'<b style="color:green">Email updated!</b>'
            )
        ),
        'update-pass'=>array(
            'title'=>'Update User Password',
            'description'=>'Change User Password.',
            'labels'=>array(
                'old-pass'=>'Old Password:',
                'new-pass'=>'New Password:',
                'conf-pass'=>'Confirm New Password:',
                'update'=>'Update Password',
                'pass-missmatch'=>'Confirmation password is incorrect!',
                'empty-old-pass'=>'Old password cannot be empty.',
                'empty-new-password'=>'New password cannot be empty.',
                'incorrect-old-pass'=>'Incorrect old password!',
                'updated'=>'<b style="color:green">Password updated!</b>'
            )
        ),
        'view-users'=>array(
            'title'=>'System Users',
            'description'=>'List of System Users.',
            'labels'=>array(
                'username'=>'Username',
                'disp-name'=>'Display Name',
                'email'=>'Email',
                'status'=>'Status',
                'reg-date'=>'Registration Date',
                'last-login'=>'Last Login'
            )
        ),
        'register'=>array(
            'title'=>'Create User Profile',
            'description'=>'Creating new profile.',
            'labels'=>array(
                'username'=>'Username:',
                'password'=>'Password:',
                'conf-pass'=>'Confirm Password:',
                'email'=>'Email:',
                'disp-name'=>'Display Name:',
                'reg'=>'Register',
                'acc-lvl'=>'Access Level:'
            ),
            'errors'=>array(
                'missing-acc-lvl'=>'Select Access Level!',
                'missing-username'=>'Username cannot be empty.',
                'missing-pass'=>'Password cannot be empty.',
                'pass-missmatch'=>'Check your confirmed password.',
                'missing-email'=>'Email cannot be empty.'
            )
        ),
    ),
    'aside'=>array(
        'logout'=>'Logout',
        'home'=>'Home Page',
        'profile'=>'User Profile',
        'add-user'=>'Create User',
        'view-users'=>'View Users',
        'sys-info'=>'System Info'
    ),
    'api-messages'=>array(
        
    )
);