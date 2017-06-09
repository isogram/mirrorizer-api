<?php

namespace App;

/**
* Constants
* 
*/
class Constant
{
    // base
    const SUCCESS_TO_FETCH_ITEM         = 'OK';
    const FAILED_VALIDATION             = 'Validation fails';
    const SERVER_ERROR                  = 'Internal Error';
    const SUCCESS_TO_CREATE_ITEM        = 'Success to create item';
    const SUCCESS_TO_UPDATE_ITEM        = 'Success to update item';
    const FAILED_TO_PROCESS             = 'Failed to process request';

    // users
    const FAILED_TO_CREATE_USER         = 'Failed to register new user';
    const SUCCESS_TO_CREATE_USER        = 'Success to register new user';
    const FAILED_LOGIN                  = 'Email or password doesn\'t matched';
    const SUCCESS_LOGIN                 = 'Login success';
    const SUCCESS_TO_RESET_PASSWORD     = 'Check your email (inbox / spam folder). We\'ll sent you the new password if you\'re registered';
    const CURRENT_PASSWORD_NOT_MATCHED  = 'Current password not matched';
    const SUCCESS_TO_CHANGE_PASSWORD    = 'Success to change password';
    const SUCCESS_TO_CHANGE_EMAIL       = 'Success to change email';
}