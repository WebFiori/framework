<?php
/**
 * A language file that represents English language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
const LANGUAGE = array(
    'general'=>array(
        'wait'=>'Please wait a moment 🙂',
        'loading'=>'Loading...'
    ),
    'pages'=>array(
        'login'=>array(
            'description'=>'Login to the system.',
            'title'=>'Login Page',
            'success'=>'You are logged in.',
            'keep-me-logged'=>'Keep me logged in for one week.',
            'actions'=>array(
                'login' => 'Login',
            ),
            'labels'=>array(
                'main'=>'Login to The System',
                'username' => 'Username or email:',
                'password' => 'Password:',
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