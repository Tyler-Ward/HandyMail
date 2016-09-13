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
 * Handymail - Secure class.
 *
 * This class handles secure form element specific validation & Filtering
 * @package HandyMail
 * @author I.G Laghidze <developer@firewind.co.uk>
 */
class Handymail_Secure {
    
    /**
     * Applies the specified input filters to the supplied input and returns the modified string.
     * @param string $input The input string.
     * @param array $filters An array of filters to be parsed.
     * @param boolean $xss If true, applies escape_html, strip_html and trim_input methods regardless.
     * @return string The modified input string.
     */
    static public function apply_filters($input, $filters) {
        foreach($filters as $filter) {
            switch($filter) {
                case "strip_html":
                    $input = self::strip_html($input);
                    break;
                case "escape_html":
                    $input = self::escape_html($input);
                    break;
                case "trim":
                    $input = self::trim_input($input);
                    break;
                case "hash": 
                    $input = self::hash_input($input);
                    break;
                case "nl2br":
                    $input = self::nl2br_recursive($input);
                    break;
            }
        }
        if (Handymail::$xss) {
            return self::escape_html(self::strip_html(self::trim_input($input)));
        }
        return $input;
    }
    
    
    /**
     * Applies the specified validation rules to the input.
     * @param string $input The submitted field input.
     * @param string $id The field ID.
     * @param array $fields Reference array of defined field objects.
     * @param array $rules Array of rules
     * @return array An array of errors. If empty, validation has passed.
     */
    static public function apply_validation($input, $id,  &$fields, $rules) {
        $errors = array();
        $field = $fields[$id];
        foreach($rules as $rule) {
            switch(true) {
                case ($rule == "required"):
                    if (!Handymail_Secure::required($input)) {
                        if($field->type == "checkbox" or $field->type =="radio") {
                            $errors[$field->id] = sprintf("Please select %s.", ucfirst($field->name));
                        }
                        else {
                            $errors[$field->id] = sprintf("%s is a required field.", ucfirst($field->name));
                        }
                        return $errors;
                    }
                    break;
                case ($rule == "numeric"):
                    if (!Handymail_Secure::numeric($input) && $input != false) {
                        $errors[$field->id] = sprintf("Input for %s should be numeric only.", ucfirst($field->name));
                        return $errors;
                    }
                    break;
                case ($rule == "alphanumeric"):
                    if (!Handymail_Secure::alphanumeric($input) && $input != false) {
                        $errors[$field->id] = sprintf("Input for %s should be alphanumeric only.", ucfirst($field->name));
                        return $errors;
                    }
                    break;
                // Limit rule
                case (preg_match("/^limit\{[0-9]+\}$/", $rule)):
                    preg_match("/(?<=\{)[0-9]+(?=\})/", $rule, $limit);
                    $limit = $limit[0];
                    if (!Handymail_Secure::limit($input, $limit) && $input != false) {
                        $errors[$field->id] = sprintf("Maximum allowable characters (%s) exceeded.", intval($limit));
                        return $errors;
                    }
                    break;
                case ($rule == "valid_email"):
                    if (!Handymail_Secure::valid_email($input) && $input != false) {
                        $errors[$field->id] = "Please supply a valid email address.";
                        return $errors;
                    }
                    break;
                // Match rule
                case (preg_match("/^match\{[A-Za-z0-9_]+\}$/", $rule)):
                    preg_match("/(?<=\{)[A-Za-z0-9_]+(?=\})/", $rule, $match);
                    $match = Handymail::$input_prefix . $match[0];
                    if (!isset($_POST[$match])) {
                        $errors[$field->id] = "Invalid match ID for $field->name: $match does not exist.";
                        return $errors;
                    }
                    elseif (!Handymail_Secure::match($input, $_POST[$match]) && $input != false) {
                        $errors[$field->id] = "Field does not match {$fields[$match]->name}.";
                        return $errors;
                    }
                    break;
                // Match forbidden keywords
                case (preg_match("/^forbidden\{[A-Za-z0-9_\-, :<>]+\}$/", $rule)):
                    preg_match("/(?<=\{)[A-Za-z0-9_\-, :<>]+(?=\})/", $rule, $keys);
                    $keys = explode(",", $keys[0]);
                    // If matches exist, then throw error.
                    if($matches = self::forbidden($input, $keys)) {
                        $errors[$field->id] = "Field contains the following forbidden keywords: (" . implode(", ", $matches) . ")";
                    }
                    return $errors;
                    break;
            }  
        }
        return $errors;
    }


    /**
     * Recursively checks for forbidden keywords in supplied input.
     * @param string|array $input The submitted field input.
     * @param array $keys An array of forbidden keywords.
     * @return boolean If false, no forbidden keywords found - otherwise returns an array of 
     * forbidden keywords (evaluating to boolean true). **IMPORTANT** that false is good here.
     */
    static private function forbidden($input, $keys) {
        $input = serialize($input);
        $culprits = array();
        foreach($keys as $key) {
            if (strpos($input, $key) !== FALSE) {
                $culprits[] = $key;
            }
        }
        return (!empty($culprits)) ? $culprits : false;
    }


    /**
     * Recursively hashes the submitted input.
     * @param string|array $input The submitted field input.
     * @return string|array The hashed field input.
     * @access private
     */
    static private function hash_input($input) {
        if(is_array($input)) {
            foreach($input as $key => $val) {
                $input[$key] = self::hash_input($val);
            }
            return $input;
        }
        else {
            return password_hash($input, PASSWORD_DEFAULT);
        }
    }
    
    
    /**
     * Recursively trims the submitted input of all whitespace left and right.
     * @param string|array $input The submitted field input.
     * @return string|array The trimmed field input.
     * @access private
     */
    static private function trim_input($input) {
        if(is_array($input)) {
            foreach($input as $key => $val) {
                $input[$key] = trim($val);
            }
            return $input;
        }
        else {
            return trim($input);
        }
    }
    
    
    /**
     * Recursively runs strip_html on the submitted input.
     * @param string|array $input The submitted field input.
     * @return string|array The filtered field input with stripped HTML tags.
     * @access private
     */
    static private function strip_html($input) {
        if(is_array($input)) {
            foreach($input as $key => $val) {
                $input[$key] = self::strip_html($val);
            }
            return $input;
        }
        else {
            return strip_tags($input);
        }
    }
    
    
    /**
     * Recursively runs escape_html() on the submitted input.
     * @param string|array $input The submitted field input.
     * @return string|array The filtered field input with escaped html tags.
     * @access private
     */
    static private function escape_html($input) {
        if(is_array($input)) {
            foreach($input as $key=>$val) {
                $input[$key] = self::escape_html($val);
            }
            return $input;
        }
        else {
            return htmlspecialchars($input);
        }
    }
    
    
    /**
     * Recursively runs nl2br() on the submitted field input. Extremely useful for textarea fields, but runs on all.
     * @param string|array $input The submitted field input.
     * @return string|array The modified field input.
     * @access private
     */
    static private function nl2br_recursive($input) {
        if(is_array($input)) {
            foreach($input as $key=>$val) {
                $input[$key] = self::nl2br_recursive($val);
            }
            return $input;
        }
        else {
            return nl2br($input);
        }    
    }
    
    
    /**
     * Validation checks if a submission is not empty.
     * @param string|array $input The submitted field input.
     * @return boolean True if not empty, false if empty.
     * @access private
     */
    static private function required($input) {
        if(is_array($input)) {
            return (!empty($input)) ? true : false;
        }
        else {
            return ($input != "") ? true : false;
        }
    }
    
    
    /**
     * Recursively runs a check to see if submitted inputs match the specified value.
     * @param string|array $input The submitted field input.
     * @param string The value to match
     * @return boolean True if matched, false upon failure.
     * @access private
     */
    static private function match($input, $match) {
        if(is_array($input)) {
            foreach($input as $key => $val) {
                if(!self::match($val, $match)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return ($input === $match) ? true : false;
        }
    }
    
    
    /**
     * Recursively checks if a submission is numeric only.
     * @param string|array $input The submitted field input.
     * @return boolean True if validation passes, false upon failure.
     * @access private
     */
    static private function numeric($input) {
        if(is_array($input)) {
            foreach($input as $value) {
                if(!self::numeric($value)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return is_numeric($input);
        }
    }
    
    
    /**
     * Recursively checks if a submission is alphanumeric.
     * @param string|array $input The submitted field input.
     * @return boolean True if validation passes, false upon failure.
     * @access private
     */
    static private function alphanumeric($input) {
        if(is_array($input)) {
            foreach($input as $value) {
                if(!self::alphanumeric($value)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return preg_match("/^[A-Za-z0-9 ]+$/",$input);
        }    
    }
    
    
    /**
     * Recursively checks if the length of the submission is within the defined limit.
     * @param string|array $input The submitted field input.
     * @param int $limit The maximum number of permitted characters.
     * @return boolean True if validation passes, false upon failure.
     * @access private
     */
    static private function limit($input, $limit) {
        if(is_array($input)) {
            foreach($input as $val) {
                if(!self::limit($val, $limit)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return (strlen($input) <= intval($limit)) ? true : false;
        }
    }
    
    
    /**
     * Recursively checks if a submission has a valid email entry.
     * @param string|array $input The submitted field input.
     * @return boolean True if validation passes, false upon failure.
     * @access private
     */
    static private function valid_email($input) {
        if(is_array($input)) {
            foreach($input as $val) {
                if(!self::valid_email($val)) {
                    return false;
                }
            }
            return true;
        }
        else {
            return filter_var($input, FILTER_VALIDATE_EMAIL);
        }
    }
}