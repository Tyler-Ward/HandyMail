<?php
/**
 * HandyMail - Web form generation and processing.
 * PHP Version 5
 * @package HandyMail
 * @link https://github.com/OneBadNinja/HandyMail HandyMail Web Form Application
 * @author I.G Laghidze <developer@firewind.co.uk>
 * @version 1.0
 * @copyright 2016 I.G Laghidze
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */


/**
 * HandyMail - Main class.
 *
 * The main class handles generating the form HTML, running the validation and sending the email.
 * @package HandyMail
 * @author I.G Laghidze <developer@firewind.co.uk>
 */
class Handymail {

    /**
     * HandyMail form name. Mandatory, as it is used as the title in the email template.
     * @var string
     */
    public $name;

    
    /**
     * Plugin version
     * @var string
     */
    const version = "1.0";
    
    
    /**
     * Input prefix property.
     *
     * Prevents clashes of ID, name and classes by applying the prefix to all form elements.
     * @var string
     */
    static public $input_prefix = "handymail_";

    
    /**
     * System error property.
     *
     * Random hash used as an index to prevent clashes with error indexes corresponding to field IDs.
     * @var string
     * @ignore
     */
    const syserror = "5109f324716cf0aad53c8c37584bc016";
    
    
    /**
     * Form action attribute value.
     * @var string
     * @access private
     */
    private $form_action;
    
    
    /**
     * Form submit value
     * @var string
     * @access private
     */
    private $submit_value;

        
    /**
     * Contains all field data in an array. Field data is appended through the add_field() method.
     * @var array
     * @access private
     * @ignore
     */
    private $fields = array();
    

    /**
     * Defined fieldsets are stored within this array with their respective labels. Corresponding input values are appended later.
     * @var array
     * @access private
     * @ignore
     */
    private $fieldsets = array();
    

    /**
     * Default field parameters. Merged with any parameters that are input.
     * @access private
     * @var array
     */
    private $default_params = array(
        "placeholder" => "",
        "reply_to" => false,
        "options" => "",
        "rows" => 10,
        "fieldset" => 0,
        "class" => ""
    );
    

    /**
     * Array where validation rules are stored.
     * They must be stored in the following format:
     * "field_id" => "rule1|rule2|rule3<argument>".
     * @var array
     * @ignore
     */
    private $validation_rules = array();
    

    /**
     * Array where filters are stored.
     * They must be stored in the following format:
     * "field_id" => "filter1|filter2|filter3".
     * @var array
     * @ignore
     */
    private $filters = array();
    
    
    
    
    /** FORM SECURITY PROPERTIES */
    
    /**
     * Captcha toggle. Uses honeypot if set to false.
     * @var boolean
     * @ignore
     */
    private $captcha = false; // use honeypot if captcha is off.
    
    
    /**
     * Google REcaptcha key
     * @var string
     * @ignore
     */
    private $captcha_key;

    /**
     * Captcha field name used for the google recaptcha element. Predefined input prefix does not apply. Do not change.
     * @var string
     * @access private
     * @ignore
     */
    private $captcha_name = "g-recaptcha-response";
    
    
    /**
     * Captcha secret key supplied by google.
     * @var string
     * @access private
     * @ignore
     */
    private $captcha_secret;

    
    /**
     * Variable to check whether to run trim/strip_html/htmlspecialchars on all fields automatically.
     * @var boolean
     */
    public static $xss = false;
    

    /**
     * Honeypot field name. Predefined input prefix will be appended.
     * @var string
     * @access private
     */
    private $honeypot_name = "h_pot";
    
    /**
     * Array of forbidden keywords which form inputs are checked against during validation.
     * @var array
     * @access private
     */
    private $forbidden_keywords = array();

    
    
    
    /******** FORM CLASS PROPERTIES ********/
    
    /**
     * Main classes for the form are predefined here. Can be reset with the set_classes() method.
     * @var array
     * @access private
     */
    private $main_class = array(
        "form" => "", 
        "fieldset" => "",
        "fieldset_label" => "",
        "fieldbox" => "",
        "field" => "",
        "submit" => "",
        "helper" => "", 
        "checkbox" => "",
        "radio" => ""
    );
    

    /**
     * Error classes that are applied to form elements are predefined here. Can be overwritten with set_error_classes()
     * @var array
     * @access private
     */
    private $error_class = array(
        "fieldbox" => "", 
        "field_label" => "", 
        "field_feedback" => ""
    );
    
    
    /**
     * Alert classes that are applied to the form message block are set here. Overwritten with set_alert_classes()
     * @var array
     * @access private
     */
    private $alert_class = array(
        "success" => "", 
        "waiting" => "", 
        "error" => ""
    );

    
    
    
    /******** EMAIL PROPERTIES ********/
    
    /**
     * The email parameters array. These are completed with prepare_mail and utilized by send_mail.
     * @var array
     * @access private
     */
    private $email_params = array(
        "recipient" => array(),
        "subject" => "",
        "from" => "",
        "reply_to" => array(),
        "body" => "",
        "attachments" => array()
    );
    
    
    /**
     * Used to check whether an email has been successfully prepared for sending or not. prepare_mail() sets to true.
     * @var boolean
     * @access private
     */
    private $mail_prepare = false;
    
    
    /**
     * Imports the PHPMailer SMTP library to send mail via an SMTP server if true.
     * @var boolean
     * @access private
     */
    private $use_smtp = false;
    

    /**
     * SMTP properties array. Set with use_smtp()
     * @var array
     * @access private
     */
    private $smtp = array(
        "Host" => "",
        "Port" => "", 
        "Username" => "", 
        "Password" => "",
        "SMTPDebug" => 0,
        "SMTPSecure" => "",
    );

    
    /**
     * Applies the necessary parameters when the form object is instantiated.
     * @param string $name Sets the form name.
     * @param string $action Sets the URL for the action attribute for the form. Can be relative/absolute.
     * @param string $submit Set the submit text for the submit button. "Submit" by default.
     * @return null
     */
    public function __construct($name, $action, $submit = "Submit") {
        $this->name = $name;
        $this->form_action = $action;
        $this->submit_value = $submit;
    }
    

    /**
     * Creates a new field object via the Handymail_Formfield class and appends it together with its parameters to the $fields array.
     * @param string $field_ A unique field identifier for the field. Input_prefix is appended.
     * @param string $field_type A field type from "text", "password", "email", "date", "select", "textarea", "radio", "checkbox".
     * @param string $label The field label. Optional.
     * @param array $params Optional field parameters within an associative array. The following keys are parsed:
     *                      - placeholder [str]
     *                      - reply_to [bool]
     *                      - options [array] Options for checkbox and radio elements.
     *                      - rows [int] Number of rows for textarea elements.
     *                      - fieldset [int] Fieldset id.
     *                      - class [str] Unique class added to the element.
     *                      - filters [str] Filters to apply to the input, a string with each filter delimited by |. Available
     *                      options include "strip_html|escape_html|trim|hash|nl2br". All options work recursively and hence
     *                      are compatible with arrays.
     *                      - rules [array] Validation rule array. If the value evaluates true, the validation applies. Parsed elements
     *                      include "required", "limit", "numeric", "alphanumeric", "valid_email", "match" - where the value is the id
     *                      of the element to match to.
     *
     * @return null
     */
    public function add_field($field_id, $field_type, $label = "", $params = array()) {
        // Create the field object via HandyMail_FormField::create and insert it into the $fields array.
        if(!preg_match("/^[A-Za-z_]+$/", $field_id)) {
            exit("Field names may only contain letters and underscores.");
        }
        $field_name = strtolower(str_replace("_", " ", $field_id));
        $field_id = self::$input_prefix . $field_id;
        $field_type = strtolower($field_type);
        // Merge the two values together. Any input $params element can overwrite the default values.
        $params = array_merge($this->default_params, $params);
        // Add field type classes to custom class.
        switch($field_type) {
            case "checkbox":
                $params["class"] = trim($this->main_class["checkbox"] . " " . $params["class"]);
                break;
                
            case "radio":
                $params["class"] = trim($this->main_class["radio"] . " " . $params["class"]);
                break;
            default:
                $params["class"] = trim($this->main_class["field"] . " " . $params["class"]);
        }
        // Check if field name exists, this will interfere with field IDs otherwise.
        if(isset($this->fields[$field_id])) {
            printf("Field '%s' already exists. Please choose another field name.", $field_name);  
            exit();
        }
        else {
            // Otherwise, create the field.
            $this->fields[$field_id] = HandyMail_FormField::instantiate($field_id, $field_name, $field_type, $label, $params);
            $this->fieldsets[intval($params["fieldset"])]["fields"][$field_id] = $field_id;
        }
    }  

    
    /**
     * Sets the label for a fieldset group.
     * @param int $gr_id ID of the revelant fieldset group - must be previously defined through add_field.
     * @param string $name Label for the fieldset.
     * @return null
     */
    public function set_fieldset_label($gr_id, $name) {
        if(isset($this->fieldsets[$gr_id])) {
            $this->fieldsets[$gr_id]["label"] = $name;
        }
        else {
            exit("Fieldset with ID $gr_id does not exist.");
        }
    }
    
    
    /**
     * Sets the prefix that will be appended to the name and id attributes of each field element. 
     *
     * This is used to prevent clashes from ambiguities arising from matching IDs.
     * @param string $input The input prefix.
     * @return null
     */
    public function set_input_prefix($input) {
        self::$input_prefix = strval($input);
    }

     
    /**
     * Configure google captcha for the send_mail method. This overrides the default honeypot security protocol.
     * @param string $secret The secret code for Google ReCaptcha
     * @param string $sitekey The assigned sitekey from Google ReCaptcha.
     * @return null
     */
    public function use_captcha($secret, $sitekey) {
        $this->captcha = true;
        $this->captcha_secret = $secret;
        $this->captcha_key = $sitekey;
    }

     
    /**
     * Changes the value of static property $xss to true.
     * @return null
     */
    static public function xss_rescue() {
        self::$xss = true;
    }
    

    /**
     * Generates HTML for the form and returns it. Utilizes the $fields and $fieldsets properties to create the corresponding inputs.
     * @param boolean $html_display If true, this returns the HTML code of the form in <pre> tags for further customization.
     * @return str The form HTML code, wrapped in <pre> tags if $html_display is set to boolean true.
     */
    public function generate($html_display = false) {
        // Check if fields have been created.
        if(empty($this->fields)) {
            // If no fields, return an error.
            return "Error: No fields have been created.";   
        }
        else {
            
            // Begin structuring form HTML.
            $formHTML = "<div id='" . self::$input_prefix . "form_message'></div>"; // Add form-message box.
            $formHTML .= "\n<form action='{$this->form_action}' method='POST' class='{$this->main_class["form"]}'>";
            
            // Loop through individual field groups. [fieldset]
            foreach($this->fieldsets as $fieldset) {
                $formHTML .= "\n\n\t<fieldset class='{$this->main_class["fieldset"]}'>";
                $formHTML .= (isset($fieldset["label"])) ? "\n\t<legend class='{$this->main_class["fieldset_label"]}'>{$fieldset["label"]}</legend>" : "";
        
                // Loop through individual fields.
                foreach($fieldset["fields"] as $id) {
                    $field = $this->fields[$id];
                    $formHTML .= "\n\t\t<div class='{$this->main_class["fieldbox"]}'>";
                    if($field->label) {
                        if(isset($this->validation_rules[$id]) && strpos($this->validation_rules[$id], "required") !== FALSE) {
                            $field->label = "* " . $field->label;
                        }
                        $formHTML .= "\n\t\t\t<label for='$field->id'>$field->label</label>";  
                        // Helper text generated here.
                        if(isset($this->validation_rules[$id])) {
                            foreach(Handymail_Formfield::helpers(explode("|", $this->validation_rules[$id])) as $helper) {
                                $formHTML .= "\n\t\t\t<p class='{$this->main_class["helper"]}'>$helper</p>";
                            }
                        }
                    }
                    // Output input code based on field type
                    $formHTML .= "\n\t\t\t" . $field->html;            
                    $formHTML .= "\n\t\t</div>";
                } // End field
                $formHTML .= "\n\t</fieldset>";
            } // End fieldset
            
            if($this->captcha) {  
                $formHTML .= "\n\n\t\t<div class='{$this->main_class["fieldbox"]}'><div class='g-recaptcha' id='" . self::$input_prefix . "_recap' data-sitekey='{$this->captcha_key}' data-expired-callback='" . self::$input_prefix . "_recap'></div></div>\n";
            }
            else {
            // Create honey pot field
                $formHTML .= "\n\n\t<div class='{$this->main_class["fieldbox"]}' style='display: none;'><input type='text' placeholder='Please leave this blank' name='{$this->honeypot_name}'/></div>";
            }
            
            // Create submit button.
            $formHTML .= "\n\t<div class='{$this->main_class["fieldbox"]}'><input type='submit' class='{$this->main_class["submit"]}' value='$this->submit_value'/></div>\n\n</form>";
            if($html_display) {
                $formHTML = "<pre>" . htmlspecialchars($formHTML) . "</pre>";   
            }
            else {
                $formHTML = trim($formHTML, "\t\n");   
            }
            return $formHTML;
        } // End else (create form)
    }
    
    
    /**
     * Parses and returns the validation scripts.
     * @param boolean $html_display If true, this returns the code in <pre> tags for further customization.
     * @return string The Javascript / jQuery code.
     */
    public function get_scripts($html_display = false) {
        $captcha = $this->captcha;
        $selectors = $this->get_selectors();
        ob_start();
        require(rtrim(dirname(__FILE__), "class") . "assets/js/handymail_scripts.raw.js.php");
        $scripts = ob_get_clean();
        if($html_display) {
            return "<pre>" . htmlspecialchars($scripts). "</pre>";
        }
        return $scripts;
    }
    
    
    /**
     * Setter method for the forbidden keywords property.
     * 
     * Forbidden keywords need to be delimited with "|".
     * @param string $words String of delimited forbidden words which will be 
     *                      appended to the current blacklist array.
     * @return null
     */
    public function set_forbidden_keywords($words) {
        $this->forbidden_keywords = array_merge($this->forbidden_keywords, explode("|", $words));
    }
    
    
    /**
     * Setter method for the validation rules property.
     * 
     * The full list of available rules is as follows:
     * "required|numeric|alphanumeric|limit{x}|valid_email|match{x}"
     * These are all applied recursively regardless of whether the input is an array or not.
     * @param array $rules The array of rules with keys corresponding to field IDs.
     *                     Each field ID corresponds to a string of rules delimited
     *                     by the "|" sign.
     * @return null
     */
    public function set_rules($rule_array) {
        if(!is_array($rule_array)) {
            exit("Method set_validation requires input argument to be an array.");
        }
        foreach($rule_array as $field_id => $rules) {
            if(!array_key_exists(self::$input_prefix . $field_id, $this->fields)) {
                exit("Field with id $field_id does not exist. Cannot set validation for it.");
            }
            // Append input prefix if necessary
            elseif(self::$input_prefix) {
                unset($rule_array[$field_id]);
                $rule_array[self::$input_prefix . $field_id] = $rules;
            }
        }
        $this->validation_rules = array_merge($this->validation_rules, $rule_array);
    }
    
    
    /**
     * Setter method for the input filters property.
     *
     * The full list of available filters is as follows:
     * "strip_html|escape_html|trim|hash|nl2br"
     * These are all applied recursively regardless of whether the input is an array or not.
     * @param array $array The array of filter rules with keys corresponding to field IDs.
     *                     Each field ID corresponds to a string of rules delimited
     *                     by the "|" sign.
     * @return null
     */
    public function set_filters($filters) {
        if(!is_array($filters)) {
            exit("Method set_validation requires input argument to be an array.");
        }
        foreach($filters as $field_id => $rule) {
            if(!array_key_exists(self::$input_prefix . $field_id, $this->fields)) {
                exit("Field with id $field_id does not exist. Cannot set validation for it.");
            }
            // Append input prefix if necessary
            elseif(self::$input_prefix) {
                unset($filters[$field_id]);
                $filters[self::$input_prefix . $field_id] = $rule;
            }
        }
        $this->filters = array_merge($this->filters, $filters);
    }

    
    /**
     * Prevent crawlers by checking whether captcha or honeypot validation passes/fails.
     *
     * Also removes captcha/honeypot input from the array to prevent being included in the processed results.
     * @param array $array Reference array (usually post) of all received inputs.
     * @return array Returns an array of errors. If they are empty, the validation has passed.
     * @access private
     */
    private function valid_signature(&$array) {
        $errors = array();
        if(isset($array[$this->captcha_name]) xor isset($array[$this->honeypot_name])) {
            if($this->captcha) {
                // Require recaptcha files.
                $dir = rtrim(dirname(__FILE__), "class");
                require_once($dir . "/libraries/recaptcha/ReCaptcha.php");
                require_once($dir . "/libraries/recaptcha/RequestMethod.php");
                require_once($dir . "/libraries/recaptcha/RequestParameters.php");
                require_once($dir . "/libraries/recaptcha/Response.php");
                require_once($dir . "/libraries/recaptcha/Curl.php");
                require_once($dir . "/libraries/recaptcha/CurlPost.php");
                require_once($dir . "/libraries/recaptcha/Post.php");
                require_once($dir . "/libraries/recaptcha/Socket.php");
                require_once($dir . "/libraries/recaptcha/SocketPost.php");
                $recaptcha = new \ReCaptcha\ReCaptcha($this->captcha_secret);
                $response = $recaptcha->verify($array[$this->captcha_name], $_SERVER["REMOTE_ADDR"]);
                if(!$response->isSuccess()) {
//                    foreach($response->getErrorCodes() as $code) {
//                        $errors[] = "{{self::syserror}}" . $code;
//                    }
                    $errors[self::syserror] = "CAPTCHA validation failed.";
                }
                unset($array[$this->captcha_name]);
            }
            else {
                if($array[$this->honeypot_name] != "") {
                    $errors[self::syserror] = "Unauthorized breach attempt. This form will <em>not</em> be sent.";
                }
                unset($array[$this->honeypot_name]);
            }
        }
        else {
            $errors[self::syserror] = "Invalid signature. Please refresh.";
        }
        return $errors;
    }
    
         
    /**
     * Check whether field names defined in the $fields array correspond to those submitted.
     *
     * This is protection against field injection, and to make sure no field names within the form have been altered. The same
     * number of inputs must be submitted as the number of defined fields.
     * @param array $array Reference array (usually post) of all received inputs.
     * @return array Returns an array of errors. If they are empty, the validation has passed.
     * @access private
     */
    private function authenticate_fields(&$array) {
        $errors = array();
        $field_count = count($this->fields);
        $matches = 0;
        foreach($this->fields as $field) {
            if(array_key_exists($field->id, $array)) {
                $matches ++;
            }
            /* If blank checkboxes and radios are sent via the form, they are not included within post.
            The below code appends empty keys to $_POST. */
            elseif($field->type == "radio" or $field->type == "checkbox") {
                $array[$field->id] = "";
                $matches ++;
            }
        }
        // Check for field injections and deletions.
        if($matches != $field_count or count($array) != $field_count) {
            $errors[self::syserror] = "Failed to authenticate fields. Please reload.";
        }
        return $errors;
    }
        
    
    /**
     * Performs a forbidden keyword check against those defined in the $forbidden_keywords property.
     * @param array $array Reference array (usually post) of all received inputs.
     * @return array Returns an array of errors. If they are empty, the validation has passed.
     * @access private
     */
    private function forbidden_keyword_check(&$array) {
        $errors = array();
        $string = serialize($array);
        foreach($this->forbidden_keywords as $fk) {
            if(strpos($string, $fk) !== FALSE) {
                $errors[self::syserror] = "Forbidden keyword: '" . htmlspecialchars($fk) . "' supplied. This form will <em>not</em> be sent.";
            }
        }
        return $errors;
    }
    
    
    /**
     * Runs all the validation and filter methods defined in the Handymail main class and the Handymail_Secure class.
     *
     * This is the only method you should run to validate inputs. Automatically checks the global $_POST array for the submitted form.
     * If form validation passes, the method applies filters and appends the filtered values to their corresponding field ids defined within
     * the $fieldsets property. 
     * If applicable, additionally populates $email_params["reply_to"] with email fields having an active "reply_to" parameter.
     * @return array An array tuple containing ($boolean, $data). If $boolean is false, $data is an array of encountered errors. If $boolean
     *               is true, $data returns the $fieldsets property for arbirary use.
     */
    public function run_validation() {
        $errors = array_merge($this->valid_signature($_POST), $this->authenticate_fields($_POST), $this->forbidden_keyword_check($_POST));
        if(empty($errors)) {
            // Check validation
            $inputs = $_POST;
            foreach($inputs as $id => $input) {
                if(isset($this->validation_rules[$id])) {
                    $errors = array_merge($errors, Handymail_Secure::apply_validation($input, $id, $this->fields, explode("|", $this->validation_rules[$id])));
                }
            }
            if(empty($errors)) {
                // If validation passes, run filters.
                foreach($inputs as $id => $input) {
                    if(isset($this->filters[$id])) {
                        $input = Handymail_Secure::apply_filters($input, explode("|", $this->filters[$id]));
                    }
                    // If xss_rescue() has been called but no filters applied, force the method.
                    elseif(Handymail::$xss) {
                        $input = Handymail_Secure::apply_filters($input, array());
                    }
                    $fieldset = $this->fields[$id]->fieldset;
                    // Assign filtered values to fields in the fieldset ID.
                    $this->fieldsets[$fieldset]["fields"][$id] = array("label" => $this->fields[$id]->label, "value" => $input);
                    // If email with reply_to, insert into 
                    if($this->fields[$id]->reply_to && $this->fields[$id]->type == "email") {
                        $this->email_params["reply_to"][] = $input;
                    }
                }
                return array(true, $this->fieldsets);
            }
            else {
                return array(false, $errors);
            }
        }
        else {
            return array(false, $errors);
        }
    }
   
 
    /**
     * Configures the use of a separate mail server (SMTP) for sending emails.
     *
     * @param array $parameters These are the SMTP parameters which are merged with the $smtp property.
     *                          The following array elements are parsed:
     *                          - Host [str] Hostname
     *                          - Port [int] Port
     *                          - Username [str] Username
     *                          - Password [str] Password
     *                          - SMTPDebug [int] default 0
     *                          - SMTPSecure [str] default "tls"
     * @return null
     */
    public function use_smtp($parameters) {
        if(!is_array($parameters)) {
            exit("Form Method USE_SMTP expects parameter 1 to be an array.");
        }
        $this->smtp = array_merge($this->smtp, $parameters);
        $this->use_smtp = true;
    }
    

    /**
     * Prepares the email by assigning input email properties to the $email_params array. Must be run before send_mail().
     *
     * Generates the body content in both HTML (via the template) and plain-text form.
     * @param array|str $recipient The email recipient(s). Can be an array of recipients or a single string with a recipient email address.
     * @param string $from The email name for the from field. Will have the server name automatically appended [name]@server.tld.
     * @param string $subject The subject field.
     * @param string $template Filename of the template to use. This template must exist within the templates directory. Defaults to "default.php".
     * @return null
     */
    public function prepare_mail($recipient, $from, $subject = "", $template = "") {
        // Set mandatory FROM and RECIPIENT variables.
        $this->email_params["from"] = sprintf("%s@%s", $from, $_SERVER["SERVER_NAME"]);
        if(!is_array($recipient)) {
            $this->email_params["recipient"][] = $recipient;
        }
        else {
            $this->email_params["recipient"] = $recipient;
        }
        // Generate email HTML body and ALTBODY via the EMAILTEMPLATE class
        $body = new Handymail_Template($this->name, $this->fieldsets);
        $this->email_params["body"] = ($template) ? $body->render($template) : $body->render();
        $this->email_params["altbody"] = $body->altbody();
           
        // Assign subject parameter. If included in $params, override the default value.
        $this->email_params["subject"] = ($subject) ? $subject : "Form submission: $this->name";
                
        // Email has been prepared
        $this->mail_prepare = true;
    }
    
     
    /**
     * If the email has been successfully prepared, attempts to send the email.
     * @return array Returns an array of errors. If they are empty, the email has been successfully sent.
     */
    public function send_mail() {
        // Check if prepare has been run yet.
        $errors = array();
        if($this->mail_prepare) {
            // Include PHPMailer - ***CREATE ABSOLUTE PATH IN CONFIG.
            require(rtrim(dirname(__FILE__), "class") . "/libraries/phpmailer/class.phpmailer.php");
            $mail = new PHPMailer();
            if($this->use_smtp) {
                require(rtrim(dirname(__FILE__), "class") . "/libraries/phpmailer/class.smtp.php");
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPDebug = $this->smtp["SMTPDebug"];
                $mail->SMTPSecure = $this->smtp["SMTPSecure"];
                $mail->Host = $this->smtp["Host"];
                $mail->Port = $this->smtp["Port"];
                $mail->Username = $this->smtp["Username"];
                $mail->Password = $this->smtp["Password"];
            }
            // Proceed with assigning properties.
            // Reply Tos if they exist.
            foreach($this->email_params["reply_to"] as $address) {
                $mail->AddReplyTo($address, $address);
            }
            $mail->SetFrom($this->email_params["from"]);
            $mail->Subject = $this->email_params["subject"];
            $mail->Body = $this->email_params["body"];
            $mail->AltBody = $this->email_params["altbody"];
            // Add Recipient Address
            foreach($this->email_params["recipient"] as $recipient) {
                $mail->AddAddress($recipient, $recipient);
            }
            // Attachments
            if(!empty($this->attachments)) {
                foreach ($this->attachments as $attachment) {
                    $mail->AddAttachment($attachment);
                }
            }
            // Send
            if(!$mail->Send()) {
                $errors[] = "Error: Email was not sent - $mail->ErrorInfo";
            }
        }
        else {
            $errors[] = "Error: send_mail() called before prepare_mail().";
        }
        return $errors;
    }

    
    /**
     * Assign new classes to the $error_class property. Merges with the default array.
     * @param array $params Parseable keys: "fieldbox", "field_label", "field_feedback".
     * @return null
     */
    public function set_error_classes($params) {
        $this->error_class = array_merge($this->error_class, $params);
    }
    
     
    /**
     * Assign new main classes to the $main_class array.
     * @param array $params Parseable keys: "form", "helper", "fieldset", "fieldset_sub", "fieldset_label", "fieldbox", "field", "submit", 
     *                      "checkbox", "radio".
     * @return null
     */
    public function set_classes($params) {
        $this->main_class = array_merge($this->main_class, $params);
    }
    
    
    /**
     * Assign new classes to the $alert_class array.
     * @param array $params Parseable keys: "success", "waiting", "error".
     * @return null
     */
    public function set_alert_classes($params) {
        $this->alert_class = array_merge($this->error_class, $params);
    }
    
    
    /**
     * Allows to quickly set predefined class profiles
     * @param string $name The style profile name. Default by default. 
     * @return null
     */
    public function set_style_profile($name = "default") {
        switch($name) {
            case "bootstrap":
                $this->main_class = array_merge($this->main_class, array(
                    "fieldbox" => "form-group",
                    "field" => "form-control",
                    "submit" => "btn btn-success", 
                    "checkbox" => "checkbox",
                    "radio" => "radio"
                ));
                $this->error_class = array_merge($this->error_class, array(
                    "fieldbox" => "has-error", 
                    "field_feedback" => "help-block"
                ));
                $this->alert_class = array_merge($this->error_class, array(
                    "success" => "alert alert-success",
                    "waiting" => "alert alert-warning",
                    "error" => "alert alert-danger"
                ));  
                break;
            default:
                $this->main_class = array_merge($this->main_class, array(
                    "form" => "handymail_form", 
                    "fieldset" => "handymail_fieldset",
                    "fieldset_label" => "handymail_fieldset_label",
                    "fieldbox" => "handymail_fieldblock",
                    "field" => "handymail_field",
                    "submit" => "handymail_submit",
                    "helper" => "handymail_helper", 
                    "checkbox" => "handymail_checkbox",
                    "radio" => "handymail_radio"
                ));
                $this->error_class = array_merge($this->error_class, array(
                    "fieldbox" => "handymail_hasError", 
                    "field_label" => "", 
                    "field_feedback" => "handymail_feedback"
                ));
                $this->alert_class = array_merge($this->error_class, array(
                    "success" => "handymail_form_success", 
                    "waiting" => "handymail_form_process", 
                    "error" => "handymail_form_error"
                ));
                break;
        }
    }
    
    
    /**
     * Exports the necessary selectors in JSON form to get parsed in javascript for usage in the validation script.
     * @return string A json encoded string to parse in javascript.
     * @ignore
     */
    public function get_selectors() {
        $prop = array(
            "error" => array(
                "fieldGroup" => $this->error_class["fieldbox"],
                "fieldFeedback" => $this->error_class["field_feedback"],
                "fieldLabel" => $this->error_class["field_label"]
            ),
            "alert" => array(
                "waiting" => $this->alert_class["waiting"],
                "success" => $this->alert_class["success"],
                "error" => $this->alert_class["error"]
            ),
            "node" => array(
                "captcha" => self::$input_prefix . "_recap",
                "formMessage" => self::$input_prefix . "form_message",
                "errortext" => self::$input_prefix . "errortext",
                "syserror" => self::syserror
            )
        );
        return json_encode($prop);
    }
    
    
    /**
     * Prints the form response in JSON format for AJAX.
     * @param array $errors - Any encountered errors to be sent back.
     * @return null
     */
    public function encode_response($errors) {
        if (empty($errors)) {
            echo json_encode(array("success" => 1, "message" => "Success! Your form has been sent!"));
        }
        else {
            echo json_encode(array("success" => 0, "message" => "You've encountered some errors with your inputs.", "errors"=>$errors));
        }
        exit();
    }
}