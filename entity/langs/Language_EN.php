<?php
/**
 * A language class that represents English language.
 * @author Ibrahim <ibinshik@hotmail.com>
 * @version 1.0
 */
class Language_EN{
    const API_M = array(
        'general'=>array(
            'query-err'=>'Database query error!'
        ),
        'login'=>array(
            
        ),
        'register'=>array(
            'success'=>'New user is registered.',
            'username-taken'=>'The given username belongs to another user. Choose another one.',
            'email-found'=>'The given email belongs to a registred user.',
            'missing-username'=>'Username is missing!',
            'missing-pass'=>'The password is missing!',
            'missing-email'=>'Email address is missing!'
        )
    );
}
/**
 * A constant array that contains UI language for the login page.
 */
const DISP_LANG_LOGIN = array(
    'wait'=>'Please wait a moment 🙂',
    'something-wrong'=>'Something went wrong. Try again in few moments. Sorry about that 😲',
    'login-main-label' => 'Programming Academia CMS Login',
    'login-label' => 'Login',
    'page-title' => 'Login',
    'cancel-label' => 'Cancel',
    'username-input-label' => 'Username or email:',
    'username-input-placeholder' => 'Enter Your Username or Email',
    'password-input-label' => 'Password:',
    'password-input-placeholder' => 'Enter Your Password Here',
    'err-incorrect-login-params' => 'Inccorrect username, email or password!',
    'err-missing-pass' => 'Missing Password!',
    'err-missing-username' => 'Missing Username!'
);
const DISP_LANG_WELCOME = array(
    'page-title'=>'Welcome to Programming Academia CMS',
    'description'=>'Since this is the first time you run the software, we need from you '
    . 'to give us few moments to do the configuration. It should not take more than 5 minuts. '
    . 'Please fill in all the required fields.',
    'admin-info-h'=>'Admin Account Information',
    'database-info-h'=>'Database Setup',
    'database-info-host'=>'Host:',
    'database-info-host-placeholder'=>'Enter the host name of the database (e.g. localhost or 100.999.199.99:3306',
    'database-info-username'=>'Username:',
    'database-info-username-placeholder'=>'The Database Username.',
    'database-info-pass'=>'Password:',
    'database-info-pass-placeholder'=>'Password:',
    'database-info-instance'=>'Schema Name:',
    'database-info-instance-placeholder'=>'The Database Instance That will be Used to Store Content.'
);      
/**
 * A constant array that contains UI language for the aside menu.
 */
const DISP_LANG_ASIDE_LINKS = array(
    'aside-logout' => 'Logout',
    'aside-new-page' => 'Create Page',
    'aside-new-group' => 'Add Group',
    'aside-new-user' => 'Add User',
    'aside-settings' => 'Settings',
    'aside-dashboard' => 'Dashboard',
    'aside-users'=>'View Users'
);
const DISP_LANG_SETTINGS = array(
    'page-title'=>'Settings'
);
/**
 * A constant array that contains UI language for the dashboard.
 */
const DISP_LANG_DASHBOARD = array(
    'page-title' => 'Dashboard',
    'all-groups-label' => 'All Groups',
    'no-data-label' => 'NO DATA',
    'groups-table-title-col' => 'Group Title',
    'groups-table-author-col' => 'Group Author',
    'groups-table-page-count-col' => '# of Pages',
    'groups-table-actions-col' => 'Actions',
    'pages-table-title-col' => 'Page Title',
    'pages-table-author-col' => 'Author',
    'pages-table-date-created-col' => 'Date Created',
    'pages-table-date-published-col' => 'Date Published',
    'pages-table-last-updated-col' => 'Last Edit',
    'pages-table-status-col' => 'Status',
    'action-delete' => 'Delete',
    'action-edit' => 'Edit',
    'action-prev' => 'Preview',
    'all-pages-label' => 'All Pages'
);
/**
 * A constant array that contains UI language for the editor.
 */
const DISP_LANG_EDITOR = array(
    'page-title' => 'Editor',
    'link-prev' => 'Preview',
    'link-dashboard' => 'Dashboard',
    'link-logout' => 'Logout',
    'link-publish' =>'Publish Page',
    'link-save-changes' => 'Apply Changes',
    'load-text' => 'Loading the page...',
    'action-new-section'=>'New Page Section'
);
/**
 * A constant array that contains UI language for the page that can be used to 
 * add new pages.
 */
const DISP_LANG_NEW_PAGE = array(
    'page-title' => 'Adding New Page',
    'title-input-label' => 'Page Title:',
    'file-name-input-label' => 'File Name:',
    'group-input-label' => 'Page Group:',
    'description-input-label' => 'Page Description',
    'desc-input-placeholder' => 'Write a Short Description for The Page.',
    'action-create-page' => 'Create Page',
    'message-valid-title' => 'Valid Title',
    'message-required-field' => 'Required Field!',
    'message-valid-file-name' => 'Valid File Name.',
    'message-invalid-file-name' => 'Invalid File Name!'
);
/**
 * A constant array that contains UI language for the page that can be used to 
 * add new groups.
 */
const DISP_LANG_NEW_GROUP = array(
    'page-title' => 'New Group',
    'title-input-label' => 'Group Title:',
    'lang-input-label' => 'Group Language:',
    'dir-input-label' => 'Group Directory:',
    'res-dir-input-label' => 'Group Resources Directory:',
    'dir-input-placeholder' => 'A folder that will contain pages after publishing.',
    'res-dir-input-placeholder' => 'A folder that will contain page resources such as images.',
    'action-create-group' => 'Create Group',
    'group-added'=>'New Group is Created.',
    'missing-group-title'=>'Group Title is Missing',
    'missing-dir'=>'Group Publishing Directory is Missing.',
    'missing-res-dir'=>'Group Resources Directory is Missing',
    'missing-group-lang'=>'Group Language is Missing.',
    'group-aready-exist'=>'A group with the same title already exist.'
);

const DISP_LANG_OTHER = array(
    'report-bug' => 'Report a Bug',
    'feedback' => 'Feedback'
);
const API_MESSAGES = array(
    //general messages
    'missing-token'=>'User access token is missing.',
    'method-not-supported'=>'Request method is not supported by the API.',
    'action-not-set'=>'The parameter action is missing.',
    'action-not-supported'=>'The given action is not supported by the API.',
    'query-error'=>'An error has accrued while sending query to the database.',
    'action-miss-match'=>'Action is for different API file.',
    'page-not-found'=>'No page has the given ID was found.',
    'not-impl'=>'Not implemented yet.',
    'not-auth'=>'You are not authorized to do that action.',
    'no-such-el'=>'No element was found which has the given ID.',
    
    //general page content messages
    'parent-not-found'=>'No parent has the given ID was found.',
    'page-desc-updated'=>'Page description updated.',
    'page-lang-updated'=>'Page language updated.',
    'missing-page-lang'=>'Page language is missing.',
    'page-title-updated'=>'The title of the page is updated.',
    'element-added'=>'New element was added to the body of the page.',
    'el-type-missmatch'=>'Trying to edit an element of different type.',
    'element-not-found'=>'No element has the given ID.',
    'element-removed'=>'Element removed succesfully.',
    'element-updated'=>'Element updated.',
    'element-not-updated'=>'Unable to update the element.',
    'missing-title'=>'The title parameter is missing.',
    'missing-page-desc'=>'The description parameter is missing.',
    'missing-page-id'=>'The parameter \'page-id\' is missing.',
    'missing-el-id'=>'The parameter \'element-id\' is missing.',
    'missing-title'=>'The parameter \'title\' is missing.',
    'missing-parent-id'=>'The parameter \'parent-id\' is missing.',
    'missing-page-lang'=>'Page language is missing.',
    
    //page paragraph related messages
    'missing-parag-body'=>'The body of the paragraph is missing.',
    
    //list related messages
    'list-created'=>'New list added.',
    'list-item-added'=>'List Item Added.',
    'missing-list-item-body'=>'List item body is missing.',
    
    //code element related messages
    'missing-code-type'=>'The type of the code is missing.',
    'missing-code'=>'The code is missing.',
    'not-supported-code'=>'Unable to format code. Type is not supported.',
    
    //html element related messages
    'missing-html-body'=>'The parameter \'content\' is missing.',
    
    //login and user related messages
    'login-success'=>'You are logged in 😄',
    'inncorrect-login-params'=>'Incorrect username, email or password 😐',
    'missing-password'=>'The password is missing 😐',
    'missing-username'=>'The username is missing 😐',
    'account-activated'=>'User account is activated successfully!',
    'account-not-activated'=>'Unable to activate user account! Check token.',
    'missing-token'=>'User token is missing.',
    
    //group related messages
    'group-exist'=>'A group with the given title already exist.',
    'group-added'=>'New group is created.',
    'missing-group-id'=>'Group identifyer is missing.',
    'group-title-updated'=>'The title of the group updated.',
    'missing-res-dir'=>'Group resources directory is missing.',
    'missing-dir'=>'Publish directory is missing.',
    'missing-group-title'=>'The title of the group is missing.',
    'group-not-removed'=>'Unable to remove group.',
    'group-removed'=>'Group removed successfully.',
    'group-not-found'=>'No group with the given ID is found.',
    
    //page related messages
    'page-published'=>'New Page Published.',
    'unlink-err'=>'Unable to remove published file.',
    'page-removed'=>'Page removed successfully.',
    'page-not-found'=>'No page with the given ID is found.',
    'page-added'=>'New page added.',
    'missing-page-title'=>'Page title is missing.',
    'missing-file-name'=>'File name is missing.',
    'missing-page-desc'=>'Page description is missing.',
    'missing-group-id'=>'Group ID is missing.',
    'missing-page-id'=>'Page ID is missing.',
    'file-name-taken'=>'The given file already exist in the group. Choose another file name.',
    
    //page content related messages
    'parent-not-found'=>'No parent element with the given ID was found.',
    
    //settings
    'valid-db-attr'=>'Connected!',
    'unable-to-connect'=>'Unable to connect to the database',
    'missing-host-name'=>'Host name is missing.',
    'setup-success'=>'Changes Saved!',
    'missing-site-name'=>'Web site name is missing!',
    'site-name-updated'=>'Web site name updated.',
    'missing-site-desc'=>'Web site description is missing!',
    'site-desc-updated'=>'Web site description updated.',
    'missing-copyright'=>'Copyright notice is missing!',
    'copyright-updated'=>'Copyright notice updated.',
    'missing-contact-email'=>'Contact email is missing!',
    'contact-email-updated'=>'Contact email updated.',
    'missing-site-author'=>'Site author is missing.',
    'site-author-updated'=>'Author name updated',
    
    //user related
    'no-user-logged'=>'No user is logged in.',
    'user-logged-out'=>'You logged out.',
    'user-already-logged'=>'A user is already logged in.'
);
const DISP_LANG_ACTIVATE = array(
    'page-title'=>'User Account Activation',
);
const DISP_LANG_NEW_USER_PAGE = array(
    'page-title'=>'Add New User',
    'username-label'=>'Username:',
    'password-label'=>'Password:',
    'email-label'=>'Email:',
    'access-level-label'=>'Access Level (1 or 2):',
    'action-create-user'=>'Create User',
);

const ADD_USER_RESPONCE_MESSAGES = array(
    'user-added'=>'New user is added.',
    'missing-password'=>'The password is missing.',
    'missing-username'=>'The username is missing.',
    'missing-email'=>'The email is missing.',
    'missing-acc-level'=>'Access level is missing.',
    'not-allowed-to-add' => 'You are not authorized to add users.',
    'username-taken'=>'Username is already taken. Choos another username.',
    'query-err'=>'An error happend while updating database.',
    'user-already-reg'=>'The given email belongs to a user who is already registered.'
);


