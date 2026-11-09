<?php

namespace Application\Service;

use Exception;
use ZipArchive;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use App\Db\Dbal;
use Doctrine\DBAL\Connection;
use Application\Session\Container;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Hackzilla\PasswordGenerator\Generator\RequirementPasswordGenerator;
use ArchiveUtil\ArchiveUtility;

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

    /**
     * Columns the add and edit forms check for duplicates, by table, with
     * each table's primary key. Nothing else can be queried.
     */
    private const array UNIQUE_FIELDS = [
        'geographical_divisions' => ['key' => 'geo_id', 'fields' => ['geo_name']],
        'roles' => ['key' => 'role_id', 'fields' => ['role_code', 'role_name']],
        'spi_rt_3_facilities' => ['key' => 'id', 'fields' => ['facility_name']],
        'users' => ['key' => 'id', 'fields' => ['login', 'email', 'contact_no']],
    ];

    /**
     * Counts the rows that already hold a value, for the duplicate checks
     * on the add and edit forms. On an edit form, fnct is
     * "<primary key>##<id>" and the row being edited is not counted.
     *
     * @throws \InvalidArgumentException for a table, column or key the
     *     forms do not use
     */
    public function checkFieldValidations($params): int
    {
        $tableName = (string) $params['tableName'];
        $fieldName = (string) $params['fieldName'];
        $value = trim((string) $params['value']);
        $fnct = (string) $params['fnct'];

        $table = self::UNIQUE_FIELDS[$tableName] ?? null;
        if ($table === null || !in_array($fieldName, $table['fields'], true)) {
            throw new \InvalidArgumentException('Not a checked field.');
        }

        $db = $this->sm->get(Connection::class);
        $query = 'SELECT COUNT(*) FROM ' . $db->quoteSingleIdentifier($tableName) . ' WHERE ' . $db->quoteSingleIdentifier($fieldName) . ' = ?';
        $values = [$value];

        if ($fnct !== '' && $fnct !== 'null') {
            [$key, $id] = array_pad(explode('##', $fnct, 2), 2, '');
            if ($key !== $table['key']) {
                throw new \InvalidArgumentException('Not the primary key.');
            }
            $query .= ' AND ' . $db->quoteSingleIdentifier($key) . ' != ?';
            $values[] = $id;
        }

        return (int) $db->fetchOne($query, $values);
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
        return $tempmailDb->insertTempMailDetails(
            toEmailAddress: $to,
            cc: $cc,
            subject: $subject,
            message: $message,
            fromName: $fromName,
            fromEmailAddress: $fromMail,
            bcc: $bcc,
        );
    }

    public function sendTempMail()
    {
        try {
            $tempMailDb = $this->sm->get('TempMailTable');
            $configResult = $this->sm->get('Config');

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

            $mailResult = $this->sm->get(Connection::class)->fetchAllAssociative("SELECT * FROM temp_mail WHERE status = 'pending' LIMIT 10");
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

                    $dirPath = self::tempFolder() . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $id;
                    if (is_dir($dirPath)) {
                        $dh = opendir(self::tempFolder() . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . $id);
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

            $mailResult = $this->sm->get(Connection::class)->fetchAllAssociative("SELECT * FROM audit_mails WHERE status = 'pending' LIMIT 10");

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

                    $dirPath = self::tempFolder() . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $id;
                    if (is_dir($dirPath)) {
                        $dh = scandir(self::tempFolder() . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $id);
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
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $globalDb = $this->sm->get('GlobalTable');
            $updateRes = $globalDb->updateConfigDetails($params);
            $subject = '';
            $eventType = 'global-config-update';
            $action = 'updated global config details';
            $resourceName = 'global-config-update';
            $eventLogDb = $this->sm->get('EventLogTable');
            $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
            $db->commit();
            $container->alertMsg = "Global Config Updated Successfully.";
        } catch (Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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
        static $cache = [];
        $key = $tablename . '|' . $fieldname . '|' . $fieldValue;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        $db = $sm->get(Connection::class);
        $row = $db->fetchAssociative(
            'SELECT * FROM ' . $db->quoteSingleIdentifier($tablename) . ' WHERE ' . $db->quoteSingleIdentifier($fieldname) . ' = ? LIMIT 1',
            [$fieldValue]
        );
        $cache[$key] = $row ?: null;
        return $cache[$key];
    }

    public static function saveFormDump($dbAdapter, $params)
    {
        $storagePath = self::privateUploadFolder('form_dump');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // ArchiveUtility picks zstd > pigz > gzip > zip based on what's
        // available on the host and appends the right extension itself.
        $dstStem = $storagePath . DIRECTORY_SEPARATOR . Uuid::uuid4()->toString() . '.json';
        $fullPath = ArchiveUtility::compressContent(json_encode($params), $dstStem);

        // The row holds the file's path, not the JSON.
        Dbal::connection($dbAdapter)->executeStatement('INSERT INTO form_dump (file_path, received_on) VALUES (?, NOW())', [$fullPath]);
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

    /**
     * The folder for files the app writes and reads back: exports, chart
     * images, email attachments. It is outside the web root, so a file
     * reaches a browser only through the download route.
     */
    public static function tempFolder(string $subfolder = ''): string
    {
        $folder = dirname(__DIR__, 5) . '/var/temporary' . ($subfolder === '' ? '' : DIRECTORY_SEPARATOR . $subfolder);
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
            throw new \RuntimeException("Cannot create $folder");
        }
        return $folder;
    }

    /**
     * An upload folder the app reads from disk only: form_dump,
     * media-attachments, import-files, facilities or template. It lives in
     * var/uploads/. A server upgraded from a version that kept it in
     * UPLOAD_PATH keeps using that copy until bin/upgrade.sh moves it, and
     * public/.htaccess refuses it meanwhile.
     */
    public static function privateUploadFolder(string $name): string
    {
        $folder = dirname(__DIR__, 5) . '/var/uploads/' . $name;
        $legacy = (defined('UPLOAD_PATH') ? UPLOAD_PATH : dirname(__DIR__, 5) . '/public/uploads') . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($folder) && is_dir($legacy)) {
            return $legacy;
        }
        if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
            throw new \RuntimeException("Cannot create $folder");
        }
        return $folder;
    }

    /**
     * The signed-in user's folder in tempFolder(), for their exports, PDFs
     * and chart images. Two users who export in the same second get files
     * with the same name, so each user has a folder of their own.
     */
    public static function userFolder(): string
    {
        $userId = (int) ((new Container('credo'))->userId ?? 0);
        if ($userId <= 0) {
            throw new \RuntimeException('No user is signed in.');
        }
        return self::tempFolder('users' . DIRECTORY_SEPARATOR . $userId);
    }

    /**
     * The path to write a file that the signed-in user then fetches from
     * the download route. The browser fetches it right away, so the user's
     * files older than a day are deleted here.
     */
    public static function downloadFile(string $fileName): string
    {
        $folder = self::userFolder();
        foreach (glob($folder . DIRECTORY_SEPARATOR . '*') ?: [] as $old) {
            if (is_file($old) && filemtime($old) < time() - 86400) {
                unlink($old);
            }
        }
        return $folder . DIRECTORY_SEPARATOR . basename($fileName);
    }

    /** The path of a file the signed-in user wrote with downloadFile(), or null. */
    public static function downloadPath(mixed $fileName): ?string
    {
        if (!is_string($fileName) || $fileName === '' || $fileName !== basename($fileName)) {
            return null;
        }
        if (empty((new Container('credo'))->userId)) {
            return null;
        }
        $path = self::userFolder() . DIRECTORY_SEPARATOR . $fileName;
        return is_file($path) ? $path : null;
    }

    /**
     * A data URI for a chart image in userFolder(), for an <img> in a TCPDF
     * template. TCPDF resolves a path in src against the web root.
     */
    public static function tempImage(string $fileName): string
    {
        $path = self::userFolder() . DIRECTORY_SEPARATOR . basename($fileName);
        if (!is_file($path)) {
            return '';
        }
        return 'data:image/png;base64,' . base64_encode((string) file_get_contents($path));
    }

    public static function embedImage($imagePath)
    {
        // The file name comes from the submission, so embed only a file
        // whose content is an image.
        if (!is_file($imagePath)) {
            return '';
        }
        $mimeType = mime_content_type($imagePath);
        if (!is_string($mimeType) || !str_starts_with($mimeType, 'image/')) {
            return '';
        }
        $base64Image = base64_encode(file_get_contents($imagePath));
        return '<img src="data:' . $mimeType . ';base64,' . $base64Image . '" style="width:100px; height:auto;" />';
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
     * The values of a multi-select filter, for a bound IN list: one ? for
     * each value.
     *
     * The pages send a selection either as an array or as a comma-separated
     * string such as "3,7". The list is never empty, because an empty list
     * gives IN (), which is a SQL error. An empty selection becomes [''] and
     * matches nothing.
     *
     * @return list<string>
     */
    public static function inList(mixed $values): array
    {
        if (is_array($values)) {
            $list = array_map('strval', array_values($values));
        } else {
            $list = array_values(array_filter(
                array_map('trim', explode(',', (string) $values)),
                static fn(string $value): bool => $value !== ''
            ));
        }

        return $list === [] ? [''] : $list;
    }

    /**
     * Escape a value for output in an HTML context.
     *
     * The audit tables hand DataTables an array of cells, and DataTables writes
     * each one as HTML; the PDF export walks the same rows into TCPDF's
     * writeHTML. Both are HTML sinks, so any value that came from an ODK
     * submission has to pass through here before it goes into a row. Cells the
     * code builds itself — action links, badges, checkboxes — stay unescaped as
     * a whole and are assembled from escaped parts instead.
     *
     * ENT_SUBSTITUTE keeps a field with a bad byte in it from escaping to an
     * empty string, which would silently blank a facility name rather than
     * showing it.
     */
    public static function escapeHtml($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

}
