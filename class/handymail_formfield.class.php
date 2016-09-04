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
 * HandyMail - FormField class.
 *
 * This class handles the generation of html form elements for the form.
 * @package HandyMail
 * @author I.G Laghidze <developer@firewind.co.uk>
 */
class Handymail_FormField {
    
    /**
     * Field name
     * @var string
     */
    public $name;


    /**
     * Field Type
     * @var string
     */
    public $type;

    /**
     * Field ID
     * @var string
     */
    public $id;

    
    /**
     * Field Label
     * @var string
     */
    public $label;

    
    /**
     * Field Placeholder
     * @var string
     */
    public $placeholder;

    
    /**
     * Field Rows - Parsed exclusively for textarea form elements.
     * @var string
     */
    public $rows;


    /**
     * An array of options for radio or checkbox form elements.
     * @var array
     */
    public $options;


    /**
     * A unique class appended to the form element. Input prefix of handymail main class applies.
     * @var string
     */
    public $css_class;


    /**
     * The fieldset group ID.
     * @var int
     */
    public $fieldset;


    /**
     * If type = email and this is set to true, the input value for this field will be appended to the reply-to array in the main class.
     * @var boolean
     */
    public $reply_to;
    

    /**
     * The HTML code for the form element
     * @var string
     */
    public $html;


    /**
     * Instantiate and return a FormField object, after populating the object parameters from the input arguments.
     * @param string $id The field ID
     * @param string $name The field name
     * @param string $type The field type
     * @param string $label The field label
     * @param array $params The parameters array that were passed to HandyMail->add_field().
     * @return object The Handymail_FormField object with assigned properties.
     */
    static public function instantiate($id, $name, $type, $label, $params) {
        $field = new self;
        $field->name = $name;
        $field->type = $type;
        $field->id = $id;
        $field->label = $label;
        $field->placeholder = $params["placeholder"];
        $field->rows = $params["rows"];
        $field->options = ($params["options"]) ? explode(",", $params["options"]) : array();
        $field->css_class = $params["class"];
        $field->fieldset = $params["fieldset"];
        $field->reply_to = ($params["reply_to"]) ? true : false;
        // Create field HTML property
        switch($field->type) {
            case "text":
            case "email":
                $field->html = self::text_field($field);
                break;      

            case "date":
                $field->html = self::date_field($field);
                break;
                
            case "select":
                $field->html = self::select_field($field);
                break;

            case "textarea":
                $field->html = self::textarea_field($field);
                break;

            case "radio":
                $field->html = self::radio_field($field);
                break;

            case "checkbox":
                $field->html = self::checkbox_field($field);
                break; 

            case "password":
                $field->html = self::password_field($field);
                break;

            default:
                $field->html .= "<p>INVALID input type specified: $field->type</p>";
                break;
        }
        return $field;
    }
    

    /**
     * Creates the text input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Text input HTML.
     * @access private
     * @ignore
     */
    static private function text_field($field) {
        return "<input type='text' class='$field->css_class' id='$field->id' name='$field->id' placeholder='$field->placeholder'/>";
    }
    

    /**
     * Creates the date field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Text input HTML.
     * @access private
     * @ignore
     */
    static private function date_field($field) {
        return "<input type='date' class='$field->css_class' id='$field->id' name='$field->id' placeholder='$field->placeholder'/>";
    }
    

    /**
     * Creates the password input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Password input HTML.
     * @access private
     * @ignore
     */
    static private function password_field($field) {
        return "<input type='password' class='$field->css_class' id='$field->id' name='$field->id'/>";
    }
    

    /**
     * Creates the select input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Select input HTML.
     * @access private
     * @ignore
     */
    static private function select_field($field) {
        if(!empty($field->options)) {
            $html = "<select class='$field->css_class' id='$field->id' name='$field->id'>";
            $html .= "<option value='' selected> - Select $field->name -</option>";
            foreach($field->options as $option) {
                $html .= "<option value='$option'>$option</option>";
            }
            $html .= "</select>";
            return $html;
        }
        else {
            return "<p>Error: You have specified no options for $field->name.</p>";
        }  
    }
    

    /**
     * Creates the textarea input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Textarea input HTML.
     * @access private
     * @ignore
     */
    static private function textarea_field($field) {
        return "<textarea rows='$field->rows' class='$field->css_class' id='$field->id' name='$field->id' placeholder='$field->placeholder'></textarea>";
    }
    

    /**
     * Creates the radio input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Radio input HTML.
     * @access private
     * @ignore
     */
    static private function radio_field($field) {
        if(!empty($field->options)) {
            $html = "<div id='$field->id'>";
            foreach($field->options as $option) {
                $html.= "<div class='$field->css_class'><label><input type='radio' name='$field->id' value='$option'>$option</label></div>";
            }
            $html .= "</div>";
            return $html;
        }
        else {
            return "<p>Error: You have specified no options for $field->name.</p>";
        }
    }
    

    /**
     * Creates the checkbox input field HTML.
     * @param object $field The instantiated field object from Handymail_FormField->instantiate().
     * @return string Checkbox input HTML.
     * @access private
     * @ignore
     */
    static private function checkbox_field($field) {
        if(!empty($field->options)) {
            $html = "<div id='$field->id'>";
            foreach($field->options as $option) {
                $html.= "<div class='$field->css_class'><label><input type='checkbox' name='$field->id[]' value='$option'/>$option</label></div>";
            }
            $html .= "</div>";
            return $html;
        }
        else {
            return "<p>Error: You have specified no options for $field->name.</p>";
        }    
    } 
    
    
    /**
     * Creates an array of helper text from certain specific validation rules.
     * @param array $rules
     * @return array The array of helpers.
     * @ignore
     */
    static function helpers($rules) {
        $helpers = array();
        foreach($rules as $rule) {
            switch(true) {
                case (preg_match("/^limit\{[0-9]+\}$/", $rule)):
                    preg_match("/(?<=\{)[0-9]+(?=\})/", $rule, $limit);
                    $helpers[] = "Maximum {$limit[0]} characters.";
                    break;
                case ($rule == "numeric"):
                    $helpers[] = "Numeric only.";
                    break;
                case ($rule == "alphanumeric"):
                    $helpers[] = "Alpha-numeric only.";
                    break;
            }
        }
        return $helpers;
    }
}