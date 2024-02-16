FUSEBOXY CAPTCHA (1.x)
======================


## Dependencies

Fuseboxy Core (1.x)
Fuseboxy Util (1.x) ===> for http-request
Google reCaptcha


## Integration

1. Register a site at Google reCAPTCHA (https://www.google.com/recaptcha)
   ===> choose *reCAPTCHA v2* and *"I'm not a robot" Checkbox*
   ===> get the _Site Key_ and _Secret Key_

2. Add reCAPTCHA config to Fuseboxy (app/config/fuseboxy_config.php)
   ===> 'captcha' => array( 'siteKey' => '{CAPTCHA_SITE_KEY}', 'secretKey'  => '{CAPTCHA_SECRET_KEY}')

3. Call *Captcha::renderClientAPI()* at <head>...</head> of HTML page

4. Render Captcha field at your form by *Captcha::renderField()*

5. Call *Captcha::validate()* after your form submission


