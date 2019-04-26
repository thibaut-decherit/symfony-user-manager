# Symfony User Manager
My take on user management with Symfony 3.4


## **Dependencies**
- Webpack Encore (SCSS and JS compilation)
- jQuery (AJAX and DOM manipulation)
- Bootstrap 4 (forms and alerts)
- Guzzle 6 (API consumption)
- [zxcvbn](https://github.com/dropbox/zxcvbn) (password strength estimation)

## **Features**

Feel free to tailor each feature to your needs.

### Internationalization:
- Content is compatible with translation files
- English translation

### Mailer:
- Service
- Account activation email
- Password reset email
- Add your own methods to send other emails

### Registration:
- Registration form with expected validations on each field (see User entity for details)
- Form submitted with AJAX to avoid refresh, so you can freely embed it in another view (e.g. in a modal)
- Custom Symfony form errors
- Bootstrap alert success with message on successful registration
- Email with activation link sent to newly registered user
  - Redirect to login page with custom flash message with Bootstrap alert success if account successfully activated
  - Redirect to login page with custom flash message with Bootstrap alert success if account already activated

### Login with Guard component:
- Login form, submitted with AJAX to avoid refresh, so you can freely embed it in another view (e.g. in a modal)
  - Username/email compatible field
- Bootstrap alert danger with message on wrong credentials
- Bootstrap alert danger with message on login attempt to unactivated account
- Redirect to login page on access attempt to page requiring authentication
- Remember me
- Guard
  - Add your own logic to what happens when user logs in successfully (e.g. redirect to specific route depending on user role)
  - Add your own logic to what happens when user fails to log in (e.g. count before account lockdown)

### Logout:
- Logout route

### User profile:
- Account information edit form with username and email fields (add fields as needed)
- Form embedded in parent view through Twig `{{ render(controler()) }}` , so you can group it together with password change form
- Form submitted with AJAX to avoid refresh
- Custom Symfony form errors
- Bootstrap alert success with message on successful edit

### Password change:
- Password change form submitted with AJAX to avoid refresh
- Form embedded in parent view through Twig `{{ render(controler()) }}` , so you can group it together with profile edit form
- Current password field
- Repeat new password field
- Custom Symfony form errors
- Bootstrap alert success with message on successful change

### Password reset:
- Password reset form with username/email compatible field
- Custom flash message with Bootstrap alert danger if no user found for submitted username/email
- Custom flash message with Bootstrap alert danger if account not yet activated
- Customizable delay between each reset request
  - Custom flash message with Bootstrap alert danger if delay has not expired, informing user of delay duration
- Customizable reset link lifetime
  - Custom flash message with Bootstrap alert danger if reset link has expired
- Email with reset link and expiration delay sent to user
- On reset success, redirect to login page and custom flash message with Bootstrap alert success

### Redirect if authenticated:
- Event listener triggered on each request through `onKernelRequest()` method 
- Redirect to homepage if authenticated user attempts to access "logged-out only" routes (e.g. login, register and password reset)
- Add your own routes and modify existing list

### Password rehash on user authentication if needed:
- Event listener triggered on login through `onSecurityInteractiveLogin` method
- Rehashes password on login if bcrypt cost has been modified in `config.yml`
  - Without this listener, cost change would apply only to password persisted (registration) or updated (password change or reset) after the change
  - This could be an issue if your existing users don't update their password
  - A workaround would be to force your users to change password but it is bad practice for multiple reasons and you could have to deal with distrust ("Why are you asking me that? Have you been hacked? Are my data safe?")
  - This listener prevents all that by working seamlessly in the backgroup while your users log in
- Password checked through `password_needs_rehash`  method
- Bcrypt implementation
- Modify listener and config files to implement another algorithm. According to `password_needs_rehash` documentation it should work even if you switch hashing algorithm in production environment

### Haveibeenpwned API password validator:
- Prevents your users from choosing a password compromised in known data breaches
- Password validation through Troy Hunt [haveibeenpwned.com](https://haveibeenpwned.com/) API
- Custom Symfony form error
- Consider implementing this through something less strict than a validator if you think it could deter potential users (e.g. an informative message on user profile)

### Password strength meter:
- Usable separately or conjointly with the back-end HIBP password validator
- Visual indicator ONLY, to help your users choose a "good" password
- Password strength is based on length, [zxcvbn](https://github.com/dropbox/zxcvbn) password strength estimator from Dropbox and a check against previously leaked passwords through Troy Hunt [haveibeenpwned.com](https://haveibeenpwned.com/) API (if available)

### Unactivated accounts removal command:
- Command to delete users registered for more than `d` days if they haven't activated their account
- Removes accounts that will most probably never be used
- Modify time between registration and removal as needed
- Execute `php bin/console app:remove-unactivated-accounts-older-than d` command (e.g. through a cron job)

### Response header setter:
- Event listener triggered on each response through `onKernelResponse()` method
- Adds custom headers to the response
- Support for "static" headers specified in `config.yml`
  - Currently includes security / privacy related headers:
    - Referrer-Policy
    - X-Content-Type-Options
    - X-Frame-Options
    - X-XSS-Protection
- Support for "dynamic" headers generated according to specific parameters (app environment, requested route...)
  - Currently includes a Content Security Policy header generator and setter:
    - Allows you to protect your users from malicious resources (e.g. malicious JavaScript code that could end up in your dependencies, like [this one](https://blog.npmjs.org/post/180565383195/details-about-the-event-stream-incident))
    - Two level policy, normal & strict, in case you want to make sure critical routes are better protected (e.g. your website consumes an API with Ajax/fetch or requires a CDN for specific features, but you want to make sure this API or CDN cannot compromise your most critical routes, like login or checkout, if they ever become compromised [themselves](https://www.troyhunt.com/the-javascript-supply-chain-paradox-sri-csp-and-trust-in-third-party-libraries/))
    - Add your own routes to the list of those requiring strict policy
    - Customizable directives for each policy level (modify existing ones, add your own)
    - Dev environment directives to generate (less secure) directives allowing Symfony Profiler to work properly. The Profiler relies on inline JS and CSS, which you are strongly advised to block in production environment to counter XSS. Current whitelists block these by default in production environment.