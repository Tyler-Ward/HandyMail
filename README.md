![logo](http://firewind.co.uk/handymail/assets/images/logo.png)


[HandyMail](http://firewind.co.uk/handymail) is a flexible PHP form script that allows you quickly to generate functional, back-end
validated forms for your website without too much hassle. 

HandyMail requires PHP version 5.5+. Check yoself before you wreck yoself with ```phpinfo();```.

<h2 id="features">Features</h2>

- Create a variety of dynamic forms without writing too much code.
- Built in back-end validation & filters.
- Use of AJAX technology with JSON for dynamic processing.
- Utilizes the powerful [PHPMailer] (https://github.com/PHPMailer/PHPMailer) library for sending mail.
- Easy SMTP and Google ReCaptcha setup.
- Fully customizeable -> Supports raw HTML output & custom stylesheet selectors.
- Does your site use bootstrap? Quickly apply bootstrap classes to your forms with a single line of code.
- Packed with clean email template for sending organised form submissions.

View a demonstration of forms generated via HandyMail @ [HandyMail V1.0 Demo](http://firewind.co.uk/handymail).

<h2 id="start">Getting started</h2>
#### Installation
Download the _HandyMail-master.zip_ file and unzip into your root directory. For the sake of this demonstration, assume we rename the unzipped folder to _handymail_.

#### Basic Setup

Import the HandyMail library files:

```php
require_once("handymail/handymail.init.php");
```


HandyMail utilizes AJAX technology for validating forms, and hence requires the jQuery library to function. If you don't already use jQuery, include the following CDN within the `<head>` section of your form page:

```html
<head>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>

```

If you want to use the default handymail stylesheet, add the following too:
```html
<link rel="stylesheet" type="text/css" href="handymail/assets/css/handymail.css"/>
```

To learn how to create your forms, proceed to the [HandyMail wiki](https://github.com/OneBadNinja/HandyMail/wiki)

<h2 id="example">Example usage: A basic contact form page</h2>

Create a file called ```contact.php``` in your root folder.

```php
<?php

    require_once("includes/handymail/handymail.init.php");
    $form = new Handymail("Contact Us", "contact.php", "Send Message");
    $form->set_style_profile(); // Use default styles.
    
    // Add form fields
    $form->add_field("name", "text", "Your Name");
    $form->add_field("email" , "email", "Email Address");
    $form->add_field("message", "textarea", "Your message");
    
    // Set validation rules
    $form->set_rules(array(
      "name" => "required",
      "email" => "required|valid_email",
      "message" => "required"
    ));
    
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // Empty $errors array to populate with any returned errors. 
        $errors = array();
        $form->xss_rescue(); // Sanitize inputs
        list($success, $data) = $form->run_validation();
        if($success) {
	  // Replace recipient@site.tld with TO email and admin with prefix of FROM email ie [admin]@yoursite.com
          $form->prepare_mail("recipient@site.tld", "admin");
          // Return an array of errors. If blank, $errors will be blank too.
          $errors = $form->send_mail();
        }
        else {
          $errors = $data;
        }
        // Prepare response messages in JSON.
        $form->encode_response($errors);
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <!-- default stylesheet -->
    <link rel="stylesheet" type="text/css" href="includes/handymail/assets/css/handymail.css"/>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
</head>
<body>
    <h1><?php echo $form->name; ?></h1>
    <!-- Generate form HTML -->
    <?php echo $form->generate(); ?>
    <!-- Generate form scripts -->
    <?php echo $form->get_scripts(); ?>
</body>
</html>
```


<h2 id="documentation">Documentation</h2>
The [HandyMail wiki](https://github.com/OneBadNinja/HandyMail/wiki) is a useful resource that covers important topics to facilitate its usage. You may skip right ahead to certain topics below:
- [Creating your first form fields]()
- [Using Validation & Filters]()
- [Configuring Form Selectors]()
- [Sending Mail]()
- [SMTP setup]()
- [Google ReCaptcha setup]()

Computer-generated documentation provided by PHPDocumentor is also available, please visit [HandyDocs](onebadninja.github.io/handymail)

<h2 id="contribute">Contribute</h2>
You may contribute any improvements and fixes via regular pull-requests. These will be reviewed as per standard procedure before integration.

<h2 id="license">License</h2>
HandyMail is licensed under the GNU Lesser General Public License (LGPL) - see the LICENSE.md file for details.
Alternatively you may visit [http://www.gnu.org/copyleft/lesser.html](http://www.gnu.org/copyleft/lesser.html)

<h2 id="contact">Authors</h2>
**I.G Laghidze** (Founder) - developer@firewind.co.uk
