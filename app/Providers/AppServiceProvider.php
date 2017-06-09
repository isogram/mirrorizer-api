<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

use Auth;
use App\Models\Directory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Check the ID is belongs to the user
         * 
         * @param string 'is_my_folder' name of attribute
         * @param string $value value of the request 
         * @param array $parameters needs 1 param and it's user ID
         * @param type $validator 
         * @return bool
         */
        Validator::extend('is_my_folder', function ($attribute, $value, $parameters, $validator) {
            $countParams = count($parameters);

            if ($countParams != 1) {

                return false;

            } else {

                $userId = $parameters[0];

                $dir = Directory::where('member_id', $userId)->where('id', $value)->count();

                return $dir > 0 or $value == 0 ? true : false;
            }
        });

        Validator::replacer('is_my_folder', function ($message, $attribute, $rule, $parameters) {
            return "Directory is not belongs to you!";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
