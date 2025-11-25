<?php

namespace Application\Service;

use Exception;
use ZipArchive;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Session\Container;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Hackzilla\PasswordGenerator\Generator\RequirementPasswordGenerator;

class CommonService
{

    public $sm = null;
    public $adapter = null;

    public function __construct($sm = null)
    {
        if ($sm === null) {
            throw new Exception("Service Manager cannot be null");
        }
        $this->sm = $sm;
    }

    public function getServiceManager()
    {
        return $this->sm;
    }

    public static function generateRandomString($length = 32)
    {
        // Possible seeds
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $randomString .= $character;
        }
        return $randomString;
    }

    public function checkFieldValidations($params)
    {

        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
        $configResult = $this->sm->get('Config');
        $tableName = $params['tableName'];
        $fieldName = $params['fieldName'];
        $value = trim($params['value']);
        $fnct = $params['fnct'];
        try {
            $sql = new Sql($adapter);
            if ($fnct == '' || $fnct == 'null') {
                $select = $sql->select()->from($tableName)->where(array($fieldName => $value));
                //$statement=$adapter->query('SELECT * FROM '.$tableName.' WHERE '.$fieldName." = '".$value."'");
                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();
                $data = count($result);
            } else {
                $table = explode("##", $fnct);
                if ($fieldName == 'password') {
                    //Password encrypted
                    $password = sha1($value . $configResult["password"]["salt"]);
                    $select = $sql->select()->from($tableName)->where(array($fieldName => $password, $table[0] => $table[1]));
                    $statement = $sql->prepareStatementForSqlObject($select);
                    $result = $statement->execute();
                    $data = count($result);
                } else {
                    // first trying $table[1] without quotes. If this does not work, then in catch we try with single quotes
                    //$statement=$adapter->query('SELECT * FROM '.$tableName.' WHERE '.$fieldName." = '".$value."' and ".$table[0]."!=".$table[1] );
                    $select = $sql->select()->from($tableName)->where(array("$fieldName='$value'", "$table[0]!=$table[1]"));
                    $statement = $sql->prepareStatementForSqlObject($select);
                    $result = $statement->execute();
                    $data = count($result);
                }
            }
            return $data;
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public static function verifyIfDateValid($date): bool
    {
        $date = trim($date);
        $response = false;

        if ($date === '' || 'undefined' === $date || 'null' === $date) {
            $response = false;
        } else {
            try {
                $dateTime = new DateTimeImmutable($date);
                $errors = DateTimeImmutable::getLastErrors();
                if (empty($dateTime) || $dateTime === false || !empty($errors['warning_count']) || !empty($errors['error_count'])) {
                    //error_log("Invalid date :: $date");
                    $response = false;
                } else {
                    $response = true;
                }
            } catch (Exception $e) {
                //error_log("Invalid date :: $date :: " . $e->getMessage());
                $response = false;
            }
        }

        return $response;
    }

    // Returns the given date in Y-m-d format
    public static function isoDateFormat($date, $includeTime = false)
    {
        $date = trim($date);
        if (false === self::verifyIfDateValid($date)) {
            return null;
        } else {
            $format = "Y-m-d";
            if ($includeTime === true) {
                $format .= " H:i:s";
            }
            return (new DateTimeImmutable($date))->format($format);
        }
    }


    // Returns the given date in d-M-Y format
    // (with or without time depending on the $includeTime parameter)
    public static function humanReadableDateFormat($date, $includeTime = false, $format = "d-M-Y")
    {
        $date = trim($date);
        if (false === self::verifyIfDateValid($date)) {
            return null;
        } else {

            if ($includeTime === true) {
                $format .= " H:i";
            }

            return (new DateTimeImmutable($date))->format($format);
        }
    }

    public function insertTempMail($to, $subject, $message, $fromMail, $fromName, $cc, $bcc)
    {
        $tempmailDb = $this->sm->get('TempMailTable');
        return $tempmailDb->insertTempMailDetails($to, $subject, $message, $fromMail, $fromName, $cc, $bcc);
    }

    public function sendTempMail()
    {
        try {
            $tempMailDb = $this->sm->get('TempMailTable');
            $configResult = $this->sm->get('Config');
            $dbAdapter = $this->sm->get('Laminas\\Db\\Adapter\\Adapter');
            $sql = new Sql($dbAdapter);

            // Setup SMTP transport using Symfony Mailer DSN
            $ssl = $configResult["email"]["config"]["ssl"];
            $scheme = ($ssl === 'tls') ? 'smtp' : (($ssl === 'ssl') ? 'smtps' : 'smtp');
            $dsn = sprintf(
                '%s://%s:%s@%s:%s',
                $scheme,
                urlencode($configResult["email"]["config"]["username"]),
                urlencode($configResult["email"]["config"]["password"]),
                $configResult["email"]["host"],
                $configResult["email"]["config"]["port"]
            );
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $limit = '10';
            $mailQuery = $sql->select()->from(array('tm' => 'temp_mail'))->where("status='pending'")->limit($limit);
            $mailQueryStr = $sql->buildSqlString($mailQuery);
            $mailResult = $dbAdapter->query($mailQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (count($mailResult) > 0) {
                foreach ($mailResult as $result) {
                    $email = new Email();
                    $id = $result['temp_id'];
                    $tempMailDb->updateTempMailStatus($id);

                    $fromEmail = $result['from_mail'];
                    $fromFullName = $result['from_full_name'];
                    $subject = $result['subject'];

                    $email->from(sprintf('%s <%s>', $fromFullName, $fromEmail));
                    $email->replyTo($fromEmail);
                    $email->to($result['to_email']);

                    if (isset($result['cc']) && trim($result['cc']) != "") {
                        $email->cc($result['cc']);
                    }

                    if (isset($result['bcc']) && trim($result['bcc']) != "") {
                        $email->bcc($result['bcc']);
                    }

                    $email->subject($subject);
                    $email->html($result['message']);

                    $dirPath = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $id;
                    if (is_dir($dirPath)) {
                        $dh = opendir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $id);
                        while (($filename = readdir($dh)) !== false) {
                            if ($filename != "." && $filename != "..") {
                                $filePath = $dirPath . DIRECTORY_SEPARATOR . $filename;
                                $email->attachFromPath($filePath, $filename);
                            }
                        }
                        closedir($dh);
                        $this->removeDirectory($dirPath);
                    }

                    $mailer->send($email);
                    $tempMailDb->deleteTempMail($id);
                }
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            error_log('whoops! Something went wrong in send-mail.');
        }
    }

    public function sendAuditMail()
    {
        try {
            $auditMailDb = $this->sm->get('AuditMailTable');
            $spiFormV6Db = $this->sm->get('SpiFormVer6Table');
            $configResult = $this->sm->get('Config');
            $dbAdapter = $this->sm->get('Laminas\\Db\\Adapter\\Adapter');
            $sql = new Sql($dbAdapter);

            // Setup SMTP transport using Symfony Mailer DSN
            $ssl = $configResult["email"]["config"]["ssl"];
            $scheme = ($ssl === 'tls') ? 'smtp' : (($ssl === 'ssl') ? 'smtps' : 'smtp');
            $dsn = sprintf(
                '%s://%s:%s@%s:%s',
                $scheme,
                urlencode($configResult["email"]["config"]["username"]),
                urlencode($configResult["email"]["config"]["password"]),
                $configResult["email"]["host"],
                $configResult["email"]["config"]["port"]
            );
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $limit = '10';
            $mailQuery = $sql->select()
                ->from(array('a_mail' => 'audit_mails'))
                ->where("status='pending'")->limit($limit);
            $mailQueryStr = $sql->buildSqlString($mailQuery);
            $mailResult = $dbAdapter
                ->query($mailQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)
                ->toArray();

            if (!empty($mailResult)) {
                foreach ($mailResult as $result) {
                    $email = new Email();
                    $id = $result['mail_id'];
                    $auditMailDb->updateInitialAuditMailStatus($id);

                    $fromEmail = $result['from_mail'];
                    $fromFullName = $result['from_full_name'];
                    $subject = $result['subject'];

                    $email->from(sprintf('%s <%s>', $fromFullName, $fromEmail));
                    $email->replyTo($fromEmail);
                    $email->to($result['to_email']);

                    if (isset($result['cc']) && trim($result['cc']) != "") {
                        $email->cc($result['cc']);
                    }

                    if (isset($result['bcc']) && trim($result['bcc']) != "") {
                        $email->bcc($result['bcc']);
                    }

                    $email->subject($subject);
                    $email->html($result['message']);

                    $dirPath = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $id;
                    if (is_dir($dirPath)) {
                        $dh = scandir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $id);
                        // while (($filename = readdir($dh)) !== false) {
                        foreach ($dh as $filename => $value) {
                            if (!in_array($value, array(".", ".."))) {
                                $filePath = $dirPath . DIRECTORY_SEPARATOR . $value;
                                $email->attachFromPath($filePath, $value);
                            }
                        }
                        // }
                        // closedir($dh);
                        $this->removeDirectory($dirPath);
                    }

                    try {
                        $mailer->send($email);
                        $auditMailDb->updateAuditMailStatus($id);
                        if (isset($result['audit_ids']) && !empty($result['audit_ids'])) {
                            $spiFormV6Db->updateAuditMailSentStatus($result['audit_ids']);
                        }
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        error_log("Email failed for ID: " . $id);
                    }
                }
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            error_log('whoops! Something went wrong in send-audit-mail.');
        }
    }

    public static function getDateTime($format = 'Y-m-d H:i:s', $timezone = null)
    {
        $timezone = $timezone ?? date_default_timezone_get();
        return (new DateTimeImmutable("now", new \DateTimeZone($timezone)))->format($format);
    }

    public static function getDate($timezone = null)
    {
        return self::getDateTime('Y-m-d', $timezone);
    }

    public static function getCurrentTime($timezone = null)
    {
        return self::getDateTime('H:i', $timezone);
    }

    public function checkMultipleFieldValidations($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
        $jsonData = $params['json_data'];
        $tableName = $jsonData['tableName'];
        $sql = new Sql($adapter);
        $select = $sql->select()->from($tableName);
        foreach ($jsonData['columns'] as $val) {
            if ($val['column_value'] != "") {
                $select->where($val['column_name'] . "=" . "'" . $val['column_value'] . "'");
            }
        }
        //edit
        if (isset($jsonData['tablePrimaryKeyValue']) && $jsonData['tablePrimaryKeyValue'] != null && $jsonData['tablePrimaryKeyValue'] != "null") {
            $select->where($jsonData['tablePrimaryKeyId'] . "!=" . $jsonData['tablePrimaryKeyValue']);
        }
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return count($result);
    }

    public function getAllConfig($params)
    {
        $configDb = $this->sm->get('GlobalTable');
        return $configDb->fetchAllConfig($params);
    }
    public function getGlobalConfigDetails()
    {
        $globalDb = $this->sm->get('GlobalTable');
        return $globalDb->getGlobalConfig();
    }

    public function getGlobalValue($name)
    {
        $globalDb = $this->sm->get('GlobalTable');
        return $globalDb->getGlobalValue($name);
    }

    public function updateConfig($params)
    {
        $container = new Container('alert');
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $globalDb = $this->sm->get('GlobalTable');
            $updateRes = $globalDb->updateConfigDetails($params);
            $subject = '';
            $eventType = 'global-config-update';
            $action = 'updated global config details';
            $resourceName = 'global-config-update';
            $eventLogDb = $this->sm->get('EventLogTable');
            $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
            $adapter->commit();
            $container->alertMsg = "Global Config Updated Successfully.";
        } catch (Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function dbBackup()
    {
        try {
            $configResult = include(CONFIG_PATH . '/autoload/local.php');
            $dbUsername = $configResult["db"]["username"];
            $dbPassword = $configResult["db"]["password"];
            $dbName = $configResult["db"]["data-base-name"];
            $dbHost = $configResult["db"]["data-base-host"];
            $folderPath = BACKUP_PATH . DIRECTORY_SEPARATOR;

            if (!file_exists($folderPath) && !is_dir($folderPath)) {
                mkdir($folderPath);
            }
            $currentDate = date("d-m-Y-H-i-s");
            $file = $folderPath . 'odkdash-dbdump-' . $currentDate . '.sql';
            $command = sprintf("mysqldump -h %s -u %s --password='%s' -d %s --skip-no-data > %s", $dbHost, $dbUsername, $dbPassword, $dbName, $file);
            exec($command);

            $days = 30;
            if (is_dir($folderPath)) {
                $dh = opendir($folderPath);
                while (($oldFileName = readdir($dh)) !== false) {
                    if ($oldFileName == 'index.php' || $oldFileName == "." || $oldFileName == ".." || $oldFileName == "") {
                        continue;
                    }
                    $file = $folderPath . $oldFileName;
                    if (time() - filemtime($file) > (86400) * $days) {
                        unlink($file);
                    }
                }
                closedir($dh);
            }
        } catch (\Exception $exc) {
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
            error_log('whoops! Something went wrong in cron/dbBackup');
        }
    }

    function removeDirectory($dirname)
    {
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }

        // Loop through the folder
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Recurse
            $this->removeDirectory($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();
        return rmdir($dirname);
    }

    public function getAllCountries()
    {
        $db = $this->sm->get('CountriesTable');
        return $db->fetchAllCountries();
    }

    public function getSelectedLocation($id)
    {
        $db = $this->sm->get('UserLocationMapTable');
        return $db->fetchSelectedLocation($id);
    }

    public static function zipFolder($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);

                if (is_dir($file)) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file)) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source)) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
    public static function rmdirRecursive($dir)
    {
        foreach (scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }
            if (is_dir("$dir/$file")) {
                self::rmdirRecursive("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }
        rmdir($dir);
    }

    public static function smartUrlEncode($string)
    {
        // Check if the string is already URL-encoded.
        if (urlencode(urldecode($string)) === $string) {
            // The string is already URL-encoded, return as is.
            return $string;
        } else {
            // The string is not URL-encoded, encode it.
            return urlencode($string);
        }
    }

    public static function getDataFromOneFieldAndValue($tablename, $fieldname, $fieldValue, $sm = null)
    {
        return once(function () use ($tablename, $fieldname, $fieldValue, $sm) {
            $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $select = $sql->select()->from($tablename);
            $select->where([$fieldname => $fieldValue]);
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            if ($result->count() > 0) {
                $currentRow = $result->current(); // Get the current row's data as an array
                return $currentRow;
            } else {
                return null; // Return null or handle the case when no rows are found
            }
        });
    }

    public static function saveFormDump($dbAdapter, $params)
    {
        // Generate UUIDv4 for filename
        $filename = Uuid::uuid4()->toString() . '.json.gz';
        //echo $filename;

        // Define the storage path
        $storagePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'form_dump';
        $fullPath = $storagePath . DIRECTORY_SEPARATOR . $filename;

        // Ensure directory exists
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // Encode JSON and gzip it
        $jsonContent = json_encode($params);
        $gzData = gzencode($jsonContent, 9);
        file_put_contents($fullPath, $gzData);


        // SQL Insertion
        $sql = new Sql($dbAdapter);
        $insert = $sql->insert('form_dump');
        $d = array(
            'file_path' => $fullPath, // Storing the path instead of the JSON data
            'received_on' => new Expression("NOW()")
        );

        $insert->values($d);
        $selectString = $sql->buildSqlString($insert);
        $results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
    }

    public static function checkProvinceDistrict($geo_name, $sm = null)
    {
        $dbAdapter = $sm->get('Laminas\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select()->from('geographical_divisions');
        $select->where(array('geo_name' => $geo_name));
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        print_r($result);
        die;
        if ($result->count() > 0) {
            print_r(1);
            die;
            $currentRow = $result->current(); // Get the current row's data as an array
            return $currentRow;
        }
        print_r(2);
        die;
        return 0;
    }

    public static function buildSafePath($baseDirectory, array $pathComponents)
    {
        if (!is_dir($baseDirectory) && !self::makeDirectory($baseDirectory)) {
            return false; // Failed to create the directory
        }

        // Normalize the base directory
        $baseDirectory = realpath($baseDirectory);

        // Clean and sanitize each component of the path
        $cleanComponents = [];
        foreach ($pathComponents as $component) {
            // Remove dangerous characters from user-supplied components
            $cleanComponent = preg_replace('/[^a-zA-Z0-9-_]/', '', $component);
            $cleanComponents[] = $cleanComponent;
        }

        // Join the base directory with the cleaned components to create the full path
        $fullPath = $baseDirectory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $cleanComponents);

        // Check if the directory exists, if not, create it recursively
        if (!is_dir($fullPath) && !self::makeDirectory($fullPath)) {
            return false; // Failed to create the directory
        }

        return realpath($fullPath); // Clean and validated path
    }

    public static function cleanFileName($filePath)
    {
        // Extract the base file name (removes the path if provided)
        $baseFileName = basename($filePath);

        // Separate the file name from its extension
        $extension = strtolower(pathinfo($baseFileName, PATHINFO_EXTENSION));
        $fileNameWithoutExtension = pathinfo($baseFileName, PATHINFO_FILENAME);

        // Clean the file name, keeping only alphanumeric characters, dashes, and underscores
        $cleanFileName = preg_replace('/[^a-zA-Z0-9-_]/', '', $fileNameWithoutExtension);

        // Reconstruct the file name with its extension
        return $cleanFileName . ($extension ? '.' . $extension : '');
    }

    public static function makeDirectory($path, $mode = 0755, $recursive = true): bool
    {
        if (is_dir($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    public static function embedImage($imagePath)
    {
        $embedImg = '';
        if (file_exists($imagePath)) {
            // Get the embedded image in base64 format
            $base64Image = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath); // Ensure correct MIME type
            $embedImg = '<img src="data:' . $mimeType . ';base64,' . $base64Image . '" style="width:100px; height:auto;" />';
        }
        return $embedImg;
    }

    /**
     * Filters non-null values from an array.
     *
     * @param array $data The array to filter.
     * @return array Filtered array containing only non-null values.
     */
    public static function filterNonNullValues(array $data): array
    {
        if (empty($data)) {
            return []; // Return an empty array if input is null or empty
        }
        return array_filter($data, fn($value) => $value !== null);
    }

    public static function generatePassword()
    {
        $generator = new RequirementPasswordGenerator();
        $generator
            ->setLength(12)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_UPPER_CASE, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_LOWER_CASE, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_NUMBERS, true)
            ->setOptionValue(RequirementPasswordGenerator::OPTION_SYMBOLS, false)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_UPPER_CASE, 2)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_LOWER_CASE, 2)
            ->setMinimumCount(RequirementPasswordGenerator::OPTION_NUMBERS, 2);

        return $generator->generatePassword();
    }

    /**
     * Encode data to JSON safely.
     *
     * @param mixed $data
     * @param int   $options
     * @param int   $depth
     * @return string
     * @throws \JsonException
     */
    public static function jsonEncode(mixed $data, int $options = 0, int $depth = 512): string
    {
        // Default to Laminas-like behavior (escape <, >, &, ', " and throw on error)
        $options |= JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_THROW_ON_ERROR;

        return json_encode($data, $options, $depth);
    }

    /**
     * Generate a numeric OTP.
     *
     * @param int  $digits              Length of the OTP (e.g., 4–8)
     * @param bool $allowLeadingZero    If false, first digit will be 1–9
     */
    public static function generateOtp(int $digits = 6, bool $allowLeadingZero = true): string
    {
        if ($digits < 1) {
            throw new \InvalidArgumentException('OTP length must be >= 1');
        }

        $otp = '';

        if ($allowLeadingZero || $digits === 1) {
            // All digits 0–9
            for ($i = 0; $i < $digits; $i++) {
                $otp .= (string) random_int(0, 9);
            }
            return $otp;
        }

        // First digit 1–9 to avoid leading zero
        $otp .= (string) random_int(1, 9);
        for ($i = 1; $i < $digits; $i++) {
            $otp .= (string) random_int(0, 9);
        }

        return $otp;
    }
}
