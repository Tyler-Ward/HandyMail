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
 * Handymail - Template class.
 *
 * Organises filtered and validated form inputs into an email template ready for sending.
 * @package HandyMail
 * @author I.G Laghidze <developer@firewind.co.uk>
 */
class Handymail_Template {
    
    /**
     * The email title, used in the template.
     * @var string
     * @access private
     */
    private $title;
    
    
    /**
     * The processed field submissions.
     * @var array
     * @access private
     */
    private $inputs = array();
    
    
    /**
     * Assigns values to the $title and $inputs properties upon instantiation of the Handymail_Template object.
     * @param string $title The form title.
     * @param array $inputs The processed form submissions, organised into their respective fieldsets.
     * @return null
     */
    public function __construct($title, $inputs) {
        if(!is_array($inputs) or empty($inputs)) {
            exit("Inputs argument for EmailTemplate must be a populated array.");
        }
        $this->title = $title;
        $this->inputs = $inputs;
    }
    
    
    /**
     * Inserts the processed form submissions into an email template.
     * @param string $template_name The filename for the template to use. Must exist within the /templates directory.
     *                              By default, it is set to default.php
     * @return string Either the parsed template HTML or an error message.
     */
    public function render($template_name = "default.php") {
        $filepath = rtrim(dirname(__FILE__), "class") . "/templates/" . $template_name;
        if(file_exists($filepath)) {
            $title = $this->title;
            $fieldsets = $this->inputs;
            ob_start();
            require($filepath);
            return ob_get_clean();
        }
        else {
            return "Template file does not exist at $filepath";
        }
    }
    
    
    /**
     * Creates a plain text version of the email body which is made from Handymail_Template->render()
     * @return string Plain text structure of the field submissions with linebreaks.
     */
    public function altbody() {
        $text = "$this->title\n\nThe following form was submitted on " . date(DATE_COOKIE) . "\n";
        foreach($this->inputs as $fieldset) {
            $text .= ($fieldset["label"]) ? "\n\n" . $fieldset["label"] . "\n\n" : "\n\n";
            foreach($fieldset["fields"] as $field) {
                if(!is_array($field["value"])) {
                    $text .= $field["label"] . ": " . str_replace(array("<br>", "</br>", "<br/>", "<br />"), "", $field["value"]) . "\n";
                }
                else {
                    $text .= $field["label"] . "\n";
                    foreach($field["value"] as $value) {
                        $text .= " - " . str_replace(array("<br>", "</br>", "<br/>", "<br />"), "", $value) . "\n";
                    }
                }
            }
        }
        return $text;
    }
}