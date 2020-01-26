# Disable login with password

This plugin for OctoberCMS allows to mark user passwords "unset".
Users with an unset password won't be able to login until they set a new password (through the reset password form or the "Change password" page for instance).

This is useful for websites offering third-party authentification methods. For instance, when a user is registering on your website with Google, you may want to create a new account, but without a password. Thus, the user won't be able to user the login/password form, and will only be able to log in with Google, until he decides to set a password.

Also, these user won't be asked for their current password when editing their account details.

## Requirements

This plugin requires the [RainLab User](https://octobercms.com/plugin/rainlab-user) plugin.
For this plugin to be useful, you should also have other authentification methods available on your website.

## Set up

This plugin adds the attribute `password_unset` to the User model. You can check the value of this attribute to check if a user doesn't have a password.

Whenever you're registering a new user via a third-party auth method, you should set this attribute to `true`.

```php
$new_user = Auth::register([
    'email' => 'john@example.com',
    'password_unset' => true
]);
```

The `password_unset` property can also be triggered manually in the backend. Beware, when enabled on a existing user, **the old user password is erased.**

When a new password is set, the `password_unset` property is automatically turned off.

## Customize error message

When a user with a disabled password attempts to login using a login/password combo, the `auth.user_without_password_login_attempt` event is fired and an error message is displayed. You can customize the error message to guide the user on how they should log in.

```php
Event::listen('auth.user_without_password_login_attempt', function($user, &$message){
    if(/* User has linked its Google account */){
        $message = 'A matching user has been found, but the account has been created with Google. Please use your Google account to login.';
    }else{
        $message = 'No password has been set for this user. Please use the Forgot password feature.';
    }
});
```

## Account edition page

This plugin overrides the Account component from RainLab User plugin to allow edition of account details for users with no password, even if the `requirePassword` property has been enabled.

If you edited the default template of the Account component from the RainLab User plugin, make sure you still check the `updateRequiresPassword` attribute.

This attribute will be automatically set to `false` for users without password, so that they are able to edit their account details (including setting a password).
