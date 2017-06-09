<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Hashing\BcryptHasher;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

use App\Models\Member;

use App\Constant;

class MemberController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Registering New User
     * 
     * @param Request $request 
     * @param BcryptHasher $hash 
     * @return json
     */
    public function postRegister(Request $request, BcryptHasher $hash)
    {
        // trim input
        $request->merge(array_map('trim', $request->all()));

        $validator = Validator::make($request->all(), [
            'username'  => 'required|unique:members,username',
            'password'  => 'required',
            'email'     => 'required|email|unique:members,email',
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        // create new member
        $member             = new Member;
        $member->username   = $request->get('username');
        $member->password   = $hash->make($request->get('password'));
        $member->email      = $request->get('email');

        // save new member
        if ($member->save()) {

            $newMember = [
                'id'        => $member->id,
                'username'  => $member->username,
                'email'     => $member->email
            ];

            return response( $this->responseData( $newMember, false, Constant::SUCCESS_TO_CREATE_USER ) );

        }

        return response( $this->responseData([], true, Constant::FAILED_TO_CREATE_USER) , 400 );
    }

    /**
     * Login User
     * 
     * @param Request $request 
     * @param BcryptHasher $hash 
     * @return json
     */
    public function postLogin(Request $request, BcryptHasher $hash)
    {
        // trim input
        $request->merge(array_map('trim', $request->all()));

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        // find member in db
        $member = Member::where('email', $request->get('email'))->first();

        if ($member) {

            // check current user password
            $isMatched = $hash->check($request->get('password'), $member->password);

            // success login
            if ($isMatched) {

                $resp = [
                    'token' => encrypt($member->id)
                ];

                return response( $this->responseData( $resp, false, Constant::SUCCESS_LOGIN) );

            }

        }

        return response( $this->responseData([], true, Constant::FAILED_LOGIN) , 400 );
    }

    /**
     * Reset Password
     * 
     * @param Request $request 
     * @param BcryptHasher $hash 
     * @return json
     */
    public function postResetPassword(Request $request, BcryptHasher $hash)
    {

        // trim input
        $request->merge(array_map('trim', $request->all()));

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        $member = Member::where('email', $request->get('email'))->first();

        if ($member) {

            $newPassword = str_random(6);

            $member->password = $hash->make($newPassword);

            if($member->save()) {

                Mail::send(['emails.reset-html', 'emails.reset-raw'], ['member' => $member, 'new_password' => $newPassword], function ($m) use ($member) {

                    $m->to($member->email, $member->username)->subject('Reset Password!');

                });


            }

        }

        return response( $this->responseData($member, false, Constant::SUCCESS_TO_RESET_PASSWORD));

    }

    /**
     * Change Current User Password
     * 
     * @param Request $request 
     * @param BcryptHasher $hash 
     * @return json
     */
    public function postChangePassword(Request $request, BcryptHasher $hash)
    {

        // trim input
        $request->merge(array_map('trim', $request->all()));

        Validator::extend('matched_with_current', function($attribute, $value, $parameters, $validator) use ($hash) {

            return $hash->check($value, Auth::user()->password);

        }, Constant::CURRENT_PASSWORD_NOT_MATCHED );

        $validator = Validator::make($request->all(), [
            'password'          => 'required',
            'current_password'  => 'required|matched_with_current',
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        $member = Auth::user();
        $member->password = $hash->make($request->get('password'));

        if ($member->save()) {

            return response( $this->responseData($member, false, Constant::SUCCESS_TO_CHANGE_PASSWORD));

        }

    }

    /**
     * Change Current User Email
     * 
     * @param Request $request 
     * @param BcryptHasher $hash 
     * @return json
     */
    public function postChangeEmail(Request $request, BcryptHasher $hash)
    {

        // trim input
        $request->merge(array_map('trim', $request->all()));

        Validator::extend('matched_with_current', function($attribute, $value, $parameters, $validator) use ($hash) {

            return $hash->check($value, Auth::user()->password);

        }, Constant::CURRENT_PASSWORD_NOT_MATCHED );

        $validator = Validator::make($request->all(), [
            'email'             => 'required|email',
            'current_password'  => 'required|matched_with_current',
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        $member = Auth::user();
        $member->email = $request->get('email');

        if ($member->save()) {

            return response( $this->responseData($member, false, Constant::SUCCESS_TO_CHANGE_EMAIL));

        }

    }

}
