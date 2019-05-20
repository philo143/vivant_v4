<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extendImplicit('offer_price', function($attribute, $value, $parameters, $validator) {
            if($value[0] !== null){
                foreach($value as $key => $price){                    
                    if($key == 2){
                        if($price <= $value[($key-1)]){
                            return false;
                        }
                    }
                }
            }            
            return true;
        });
        Validator::extendImplicit('offer_qty', function($attribute, $value, $parameters, $validator) {
            if($value[0] !== null){
                foreach($value as $key => $qty){
                    if($qty < 1){ return false; };
                    if($key == 1 && $qty){
                        if($qty <= $value[($key-1)]){
                            return false;
                        }
                    }
                }
            }
            return true;
        });
        Validator::extendImplicit('match_block', function($attribute, $value, $parameters, $validator) {
            if($value[0] != null){
                if($value[0] !== $value[1]){
                    return false;
                } 
            }
            return true;
        });
        Validator::extendImplicit('match_digits', function($attribute, $value, $parameters, $validator) {
            $decimals = strlen(strrchr($parameters[0], '.')) -1;
            if(number_format($value,$decimals) !== $parameters[0]){
                return false;
            }                   
            return true;
        });
        Validator::replacer('match_digits', function ($message, $attribute, $rule, $parameters) {
            return str_replace(":other",$parameters[0],$message);
        });
        Validator::replacer('offer_price', function ($message, $attribute, $rule, $parameters) {
            $attribute = explode('.',$attribute);
            return str_replace(":key",$attribute[0],$message);
        });
        Validator::replacer('offer_qty', function ($message, $attribute, $rule, $parameters) {
            $attribute = explode('.',$attribute);
            return str_replace(":key",$attribute[0],$message);
        });
        Validator::replacer('match_block', function ($message, $attribute, $rule, $parameters) {
            $attribute = explode('.',$attribute);
            return str_replace(":key",$attribute[0],$message);
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
