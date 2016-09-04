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

spl_autoload_register(function ($class) {
    // To prevent confusion
    $class = strtolower($class);

    $path = dirname(__FILE__) . "/class/" . $class . ".class.php";

    if (file_exists($path)) {
        require($path);
    }	
    else {
        exit("File: " . $class . ".class.php was not found");
    }
});