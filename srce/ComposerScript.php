<?php

/**
 * @author     Marko Ivančić <marko.ivancic@srce.hr> 
 * @license    LGPL-2.1
 */

namespace Srce;

use Composer\Script\Event;

/**
 * Composer script with methods that will be called on certain events
 * that happen during composer installation/update/create-project procedures.
 */
class ComposerScript
{
    /**
     * Method that will be called after the command "composer create-project"
     * finishes. It will ask the user to set the password, techical contact
     * name, and technical contact email. It will try to write those to the
     * config/config.php file.
     * 
     * @param Event $event
     */
    public static function postCreateProject(Event $event)
    {
        // Get composer instance. We will use this to get the base dir.
        $composer = $event->getComposer();
        
        // Get the IO instance. We will use this communicate with the user over
        // the console.
	$io = $event->getIO();
        
	// IO methods are described here: https://getcomposer.org/apidoc/master/Composer/IO/IOInterface.html	
	$io->write(PHP_EOL);
		
	$io->write("                     _____        ______    _       _    _      
     /\        /\   |_   _| ____ |  ____|  | |     | |  | |     
    /  \      /  \    | |  / __ \| |__   __| |_   _| |__| |_ __ 
   / /\ \    / /\ \   | | / / _` |  __| / _` | | | |  __  | '__|
  / ____ \  / ____ \ _| || | (_| | |___| (_| | |_| | |  | | |   
 /_/    \_\/_/    \_\_____\ \__,_|______\__,_|\__,_|_|  |_|_|   
                           \____/                               
                                                                

");
        $io->write('---------------------------------------------------');
        $io->write(' Dobrodosli u instalaciju paketa simplesamlphp-aai.');
        $io->write(' Molimo unesite trazene podatke.');
        $io->write('---------------------------------------------------');

        $io->write(PHP_EOL);

        // Ask the user for password, name, email. Those will be validated by callbacks.
        // Also add slashes to ' and " chars, just in case.
        $password = addslashes($io->askAndValidate('Lozinka za kasniji pristup simplesamlphp sucelju (najmanje 6 znakova): ', 'Srce\ComposerScript::validatePassword', 5, null));
        $name = addslashes($io->askAndValidate('Ime tehnicke osobe: ', 'Srce\ComposerScript::validateName', 5, null));
        $email = addslashes($io->askAndValidate('Email tehnicke osobe: ', 'Srce\ComposerScript::validateEmail', 5, null));
        
        // Generate random salt and store it. This is the way Laravel
        // generates random string. We set it to 32 chars.
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $salt = substr(str_shuffle(str_repeat($pool, 32)), 0, 32);;
        
        // Get the base dir using the 'vendor_dir' config. Then define the path
        // to config.php file.
        $rootDir = dirname($composer->getConfig()->get('vendor-dir'));
        $configFilePath = $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        
        // If file exists, store the data.
        if (file_exists($configFilePath)) {
            // Store the file content to string.
            $fileContent = file_get_contents($configFilePath);
            // Replace default values with the user provided data.
            $fileContent = str_replace("'auth.adminpassword' => 'PromijeniMe'", "'auth.adminpassword' => '$password'", $fileContent);
            $fileContent = str_replace("'technicalcontact_name' => 'Administrator'", "'technicalcontact_name' => '$name'", $fileContent);
            $fileContent = str_replace("'technicalcontact_email' => 'na@example.org'", "'technicalcontact_email' => '$email'", $fileContent);
            $fileContent = str_replace("'secretsalt' => 'x1020wd03d24webk02pujzbenkbmeffg'", "'secretsalt' => '$salt'", $fileContent);
            
            // Try to save the file.
            if ( (file_put_contents($configFilePath, $fileContent)) != false) {
                    $io->write(PHP_EOL . '--> Podaci su upisani u datoteku: ' . $configFilePath);
            }
            else {
                    $io->writeError(PHP_EOL . '--> Nije moguce upisati konfiguracijske podatke. Molimo, provjerite podatke u datoteci config/config.php.');
            }
        }
        else {
            // Could not open the file.
            $io->writeError(PHP_EOL . '--> Nije moguce otvoriti konfiguracijsku datoteku. Molimo, provjerite podatke u datoteci config/config.php.');
        }
    }
    
    /**
     * Simple password validation, currently only checks the length.
     * 
     * @param string $password
     * @return string
     * @throws \Exception
     */
    public static function validatePassword($password)
    {
        if (mb_strlen($password) >= 6) {
            return $password;
        } 
        else {
            throw new \Exception('Lozinka mora sadrzavati najmanje 6 znakova.');
        }
    }
    
    /**
     * Simple name validation, only checks the length.
     * 
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public static function validateName($name)
    {
        if (mb_strlen($name) >= 3) {
            return $name;
        } 
        else {
            throw new \Exception('Ime mora sadrzavati najmanje 3 znaka.');
        }
    }
    
    /**
     * Check if the string is in proper email format. 
     * 
     * @param string $email
     * @return string
     * @throws \Exception
     */
    public static function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        else {
            throw new \Exception('Nije unesen ispravan email.');
        }
    }
}