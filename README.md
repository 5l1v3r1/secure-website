# Secure Website

Secure website with a registration, sign in, session management and CRUD controls.

Used PHP Data Objects (PDO) for securely accessing a database in PHP (MySQL database included).

Used CSS Flexbox for a responsive design.

No framework was used.

All the scripts are successfully validated with the [Markup Validation Service](https://validator.w3.org).

Built with security in mind.

The following attacks are prevented:

* Brute Force,
* Session Fixation,
* SQL Injection,
* Cross-Site Scripting (XSS),
* Cross-Site Request Forgery (CSRF),
* etc.

Tested on XAMPP for Windows v7.3.7 (64 bit) with Chrome v75.0.3770.142 (64-bit) and Firefox v68.0.1 (64-bit).

Made for educational purposes. I hope it will help!

## How to Run

Import ['\\db\\secure_website.sql'](https://github.com/ivan-sincek/secure-website/blob/master/db/secure_website.sql) to your database server.

Copy all the content from ['\\src\\'](https://github.com/ivan-sincek/secure-website/tree/master/src) to your server's web root directory (e.g. to '\\xampp\\htdocs\\' on XAMPP).

Change the database settings inside ['\\src\\php\\config.ini'](https://github.com/ivan-sincek/secure-website/blob/master/src/php/config.ini) as necessary.

Check the sign in credentials [here](https://github.com/ivan-sincek/secure-website/blob/master/db/test_accounts.txt).

Navigate to the website with your preferred web browser.

## Apache Hardening

**Prevent version disclosure.** From your Apache directory go to '\\conf\\extra\\httpd-default.conf' and set `ServerTokens` to `Prod` and `ServerSignature` to `Off`.

**Prevent directory listing.** From your Apache directory go to '\\conf\\httpd.conf', navigate to `DocumentRoot` section and remove `Indexes` from `Options Indexes FollowSymLinks Includes ExecCGI`.

**Prevent '/icons/' directory listing.** From your Apache directory go to '\\conf\\extra\\httpd-autoindex.conf' and comment out `Alias /icons/ "C:/xampp/apache/icons/"`.

**Disable '/server-status' page.** From your Apache directory go to '\\conf\\extra\\httpd-info.conf' and comment out entire `<Location /server-status>` element.

**Disable HTTP TRACE method.** From your Apache directory go to '\\conf\\httpd.conf', navigate to `Supplemental configuration` section and add new configuration `TraceEnable Off`.

**Prevent clickjacking attacks.** From your Apache directory go to '\\conf\\httpd.conf', navigate to `Supplemental configuration` section and add new configuration `Header set X-Frame-Options deny`.

**Set Content Security Policy HTTP response header.** The following configuration will only allow you to load resources from your own domain. From your Apache directory go to '\\conf\\httpd.conf', navigate to `Supplemental configuration` section and add new configuration `Header set Content-Security-Policy "default-src 'self';"`. Search the Internet for more Content Security Policy options.

**Block MIME sniffing.** From your Apache directory go to '\\conf\\httpd.conf', navigate to `Supplemental configuration` section and add new configuration `Header set X-Content-Type-Options nosniff`.

**Enforce cross-site scripting filter.** From your Apache directory go to '\\conf\\httpd.conf', navigate to `Supplemental configuration` section and add new configuration `Header set X-XSS-Protection "1; mode=block"`.

**Mitigate Slow Loris and other DoS attacks.** From your Apache directory go to '\\conf\\extra\\httpd-default.conf' and lower `Timeout` to `60`.

## PHP Hardening

**Prevent version disclosure.** From your PHP directory go to 'php.ini' and set `expose_php` to `Off`.

**Prevent display errors information disclosure.** From your PHP directory go to 'php.ini' and set both `display_errors` and `display_startup_errors` to `Off`.

**Set the correct server's timezone.** From your PHP directory go to 'php.ini' and set both instances of `date.timezone` to your timezone. Search the web for a list of supported timezones in PHP.

**Set the session cookie's name.** From your PHP directory go to 'php.ini' and set `session.name` to your own desired value. In addition to this website, it is also set [here](https://github.com/ivan-sincek/secure-website/blob/master/src/php/session.class.php).

**Set the session cookie's lifetime.** From your PHP directory go to 'php.ini' and set `session.cookie_lifetime` to your own desired value. In addition to this website, it is also set [here](https://github.com/ivan-sincek/secure-website/blob/master/src/php/session.class.php).

**Set the session cookie's HttpOnly flag.** The following configuration will not allow client side scripts to access the session cookie. From your PHP directory go to 'php.ini' and set `session.cookie_httponly` to `1`. In addition to this website, it is also set [here](https://github.com/ivan-sincek/secure-website/blob/master/src/php/session.class.php).

**Use strict session mode.** From your PHP directory go to 'php.ini' and set `session.use_strict_mode` to `1`.

**Disable file uploads.** Do the following only if your website does not utilize file uploads. From your PHP directory go to 'php.ini' and set `file_uploads` to `Off`.

**Prevent remote file inclusion.** From your PHP directory go to 'php.ini' and set `allow_url_fopen` to `Off`.

**Disable dangerous PHP functions.** From your PHP directory go to 'php.ini' and set `disable_functions` to `eval;exec;shell_exec;curl_exec;passthru;system;proc_open;popen`. Search the Internet for additional dangerous PHP functions.

## SSL/TLS Certificate

Find out how to create an SSL/TLS certificate [here](https://github.com/ivan-sincek/secure-website/tree/master/crt).

## Images

![Home Page](https://github.com/ivan-sincek/secure-website/blob/master/img/home.jpg)

![Registration](https://github.com/ivan-sincek/secure-website/blob/master/img/register.jpg)

![Users Table](https://github.com/ivan-sincek/secure-website/blob/master/img/users.jpg)

![Responsive](https://github.com/ivan-sincek/secure-website/blob/master/img/responsive.jpg)
