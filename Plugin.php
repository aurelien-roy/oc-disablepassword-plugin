<?php
namespace Tlokuus\DisablePassword;

use Auth;
use Event;
use Lang;
use Str;
use October\Rain\Auth\AuthException;
use System\Classes\PluginBase;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Components\Account as AccountComponent;

class Plugin extends PluginBase
{

    public $require = ['RainLab.User'];
    
    public function pluginDetails()
    {
        return [
            'name' => 'Disable Password Auth',
            'description' => 'Disable auth with password for specific users, forcing the user to choose another auth method.',
            'author' => 'Tlokuus',
            'icon' => 'icon-users'
        ];
    }

    public function boot()
    {
        /*
         * Display a message when user with disabled password try to log in
         */
        Event::listen('rainlab.user.beforeAuthenticate', function($component, $credentials){

            if(!array_key_exists('password', $credentials)){
                return;
            }

            $user = Auth::findUserByLogin($credentials['login']);

            if($user && $user->tlokuus_disablepassword_is_disabled){
                $message = Lang::get('tlokuus.disablepassword::lang.password_disabled');
                
                Event::fire('auth.user_without_password_login_attempt', [$user, &$message]);
                throw new AuthException($message);
            }
        });

        /*
         * Add "Mark password as unset" field on backend
         */
        Event::listen('backend.form.extendFields', function($widget) {

            if (!$widget->getController() instanceof \RainLab\User\Controllers\Users) {
                return;
            }

            if (!$widget->model instanceof \RainLab\User\Models\User) {
                return;
            }

            if ($widget->getContext() != 'update') {
                return;
            }

            $widget->addTabFields([
                'tlokuus_disablepassword_is_disabled' => [
                    'label'   => 'Mark password as unset',
                    'tab' => 'rainlab.user::lang.user.account',
                    'comment' => 'If option enabled, will disable ability for user to login with a password until a new password is set.',
                    'type'    => 'checkbox'
                ]
            ]);
        });

        /*
         * React to User model changes
         */
        UserModel::extend(function($model){
            $model->addFillable('tlokuus_disablepassword_is_disabled');
            $model->bindEvent('model.saveInternal', function() use ($model){
                if(array_key_exists('tlokuus_disablepassword_is_disabled', $model->getDirty()) && $model->tlokuus_disablepassword_is_disabled){
                    // Simulate an unset password by generating a random one
                    $model->password_confirmation = $model->password = Str::random(40);
                }elseif(array_key_exists('password', $model->getDirty())){
                    // Once password is changed, remove the unset flag.
                    $model->tlokuus_disablepassword_is_disabled = false;
                }
            }, 600); // Validation priority is 500. We must perform changes before validation.
        });

        /*
         * Do not ask for password when changing account details if no password is set
         */ 
        Event::listen('cms.page.initComponents', function ($controller, $page, $layout) {
            $user = Auth::getUser();
            if(!$user || $user->is_guest){
                return;
            }

            foreach($page->components as $comp){
                if($comp instanceof AccountComponent){
                    $requirePassword = $comp->property('requirePassword', false) && !$user->tlokuus_disablepassword_is_disabled;
                    $comp->setProperty('requirePassword',  $requirePassword);
                }
            }
        });
    }
}