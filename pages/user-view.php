<?php

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
if(!defined('ROOT_DIR')){
    header("HTTP/1.1 403 Forbidden");
    die(''
        . '<!DOCTYPE html>'
        . '<html>'
        . '<head>'
        . '<title>Forbidden</title>'
        . '</head>'
        . '<body>'
        . '<h1>403 - Forbidden</h1>'
        . '<hr>'
        . '<p>'
        . 'Direct access not allowed.'
        . '</p>'
        . '</body>'
        . '</html>');
}
if(UserFunctions::get()->getUserID() == -1){
    header('location: '.SiteConfig::get()->getBaseURL());
}
if(isset($_GET['id-or-username'])){
    $user = UserFunctions::get()->getUserByUsername($_GET['id-or-username']);
    if($user instanceof User){
        displayUser($user);
    }
    else if($user === UserFunctions::NO_SUCH_USER){
        $user = UserFunctions::get()->getUserByID(intval($_GET['id-or-username']));
        if($user instanceof User){
            displayUser($user);
        }
        else if($user === UserFunctions::NO_SUCH_USER){
            header("HTTP/1.1 404 Not found");
            die(''
                . '<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<title>Not Found</title>'
                . '</head>'
                . '<body>'
                . '<h1>404 - Not Found</h1>'
                . '<hr>'
                . '<p>'
                . 'The resource <b>'.Util::getRequestedURL().'</b> was not found on the server.'
                . '</p>'
                . '</body>'
                . '</html>');
        }
        else if($user == MySQLQuery::QUERY_ERR){
            header("HTTP/1.1 500 Server Error");
            die(''
                . '<!DOCTYPE html>'
                . '<html>'
                . '<head>'
                . '<title>Server Error</title>'
                . '</head>'
                . '<body>'
                . '<h1>500 - Server Error</h1>'
                . '<hr>'
                . '<p>'
                . 'An error has occurred while attempting to get data from the database.'
                . '</p>'
                . '</body>'
                . '</html>');
        }
    }
    else if($user === UserFunctions::NOT_AUTH){
        header("HTTP/1.1 403 Forbidden");
        die(''
            . '<!DOCTYPE html>'
            . '<html>'
            . '<head>'
            . '<title>Forbidden</title>'
            . '</head>'
            . '<body>'
            . '<h1>403 - Forbidden</h1>'
            . '<hr>'
            . '<p>'
            . 'You are not authorized to access the resource.'
            . '</p>'
            . '</body>'
            . '</html>');
    }
    else if($user == MySQLQuery::QUERY_ERR){
        header("HTTP/1.1 500 Server Error");
        die(''
            . '<!DOCTYPE html>'
            . '<html>'
            . '<head>'
            . '<title>Server Error</title>'
            . '</head>'
            . '<body>'
            . '<h1>500 - Server Error</h1>'
            . '<hr>'
            . '<p>'
            . 'An error has occurred while attempting to get data from the database.'
            . '</p>'
            . '</body>'
            . '</html>');
        }
}
else{
    header('location: '.SiteConfig::get()->getBaseURL().SiteConfig::get()->getHomePage());
}
/**
 * 
 * @param User $user
 */
function displayUser($user){
    Page::theme('Greeny By Ibrahim Ali');
    Page::document()->getChildByID('main-content-area')->addChild(createUserInfoView($user));
    Page::render();
}
/**
 * 
 * @param User $user
 * @return \HTMLNode
 */
function createUserInfoView($user){
    $node = new HTMLNode();
    $node->setClassName('pa-row');
    $infoNode = new HTMLNode('table');
    $infoNode->setClassName('pa-'.Page::dir().'-col-6');
    $infoNode->addChild(createUserInfoTableRow('User ID:', $user->getID()));
    $infoNode->addChild(createUserInfoTableRow('Username:', $user->getUserName()));
    $infoNode->addChild(createUserInfoTableRow('Display Name:', $user->getDisplayName()));
    $infoNode->addChild(createUserInfoTableRow('Email Address:', $user->getEmail()));
    $infoNode->addChild(createUserInfoTableRow('Registration Date:', $user->getRegDate()));
    $infoNode->addChild(createUserInfoTableRow('Last Login:', $user->getLastLogin()));
    $infoNode->addChild(createUserInfoTableRow('Last Password Reset Date:', $user->getLastPasswordResetDate()));
    $infoNode->addChild(createUserInfoTableRow('Password Reset Count:', $user->getResetCount()));
    $node->addChild($infoNode);
    return $node;
}

function createUserInfoTableRow($label,$content){
    $row = new HTMLNode('tr');
    $td1 = new HTMLNode('td');
    $td1->addChild(HTMLNode::createTextNode('<b>'.$label.'</b>'));
    $row->addChild($td1);
    $td2 = new HTMLNode('td');
    $td2->addChild(HTMLNode::createTextNode($content));
    $row->addChild($td2);
    return $row;
}