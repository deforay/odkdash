<?php

namespace Application\Model;

use App\Db\DataTables;
use App\Upload\UploadedImage;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;

class GlobalTable
{
    private readonly Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    private function update(array $data, array $where): int
    {
        return $this->db->update('global_config', $data, $where);
    }

    public function fetchAllConfig($parameters)
    {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
        $aColumns = array('display_name', 'global_value');

        /*
         * SQL queries
         * Get data to display
         */
        [$search, $values] = DataTables::search($aColumns, $parameters);
        $where = $search !== '' ? " WHERE $search" : '';
        $order = DataTables::order($aColumns, $parameters);

        $rResult = $this->db->fetchAllAssociative(
            "SELECT * FROM global_config$where" . ($order !== '' ? " ORDER BY $order" : '') . DataTables::limit($parameters),
            $values
        );
        $iFilteredTotal = (int) $this->db->fetchOne("SELECT COUNT(*) FROM global_config$where", $values);

        /* Total data set length */
        $iTotal = (int) $this->db->fetchOne('SELECT COUNT(*) FROM global_config');


        $output = array(
            "sEcho" => (int) $parameters['sEcho'],
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        foreach ($rResult as $aRow) {
            $row = [];
            $row[] = CommonService::escapeHtml(ucwords($aRow['display_name']));
            $row[] = CommonService::escapeHtml($aRow['global_value']);
            $output['aaData'][] = $row;
        }
        return $output;
    }
    public function getGlobalConfig()
    {
        return $this->db->fetchAllKeyValue('SELECT global_name, global_value FROM global_config');
    }

    /**
     * Saves the image from the form field $name, logo or additional_logo, as
     * that logo. The file name is the field name and a random string.
     */
    private function saveLogo(string $name): void
    {
        if (empty($_FILES[$name]['name'])) {
            return;
        }
        $folder = CommonService::buildSafePath(UPLOAD_PATH, ['logo']);
        $fileName = $folder === false ? null : UploadedImage::save($_FILES[$name], $folder, $name . CommonService::generateRandomString(6));
        if ($fileName !== null) {
            $this->update(['global_value' => $fileName], ['global_name' => $name]);
        }
    }

    public function updateConfigDetails($params)
    {
        $result = 0;
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['removedLogoImage']);
        $cleanedFileName = CommonService::cleanFileName($fileName);
        $cleanedFilePath = CommonService::buildSafePath(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR, []);
        if (isset($_POST['removedLogoImage']) && trim($_POST['removedLogoImage']) != "" && file_exists($cleanedFilePath . $cleanedFileName)) {
            unlink($cleanedFilePath . $cleanedFileName);
            $this->update(array('global_value' => ''), array('global_name' => 'logo'));
        }

        $this->saveLogo('logo');

        $fileName =$_POST['removedAdditionalLogoImage'];
        $cleanedFileName = CommonService::cleanFileName($fileName);

        if (isset($_POST['removedAdditionalLogoImage']) && trim($_POST['removedAdditionalLogoImage']) != "" && file_exists($cleanedFilePath.DIRECTORY_SEPARATOR.$cleanedFileName)) {
            unlink($cleanedFilePath.DIRECTORY_SEPARATOR.$cleanedFileName);
            $this->update(array('global_value' => ''), array('global_name' => 'additional_logo'));
        }

        $this->saveLogo('additional_logo');

        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['removedTempFile']);
        $cleanedFileName = CommonService::cleanFileName($fileName);
        $cleanedFilePath = CommonService::buildSafePath(CommonService::privateUploadFolder('template') . DIRECTORY_SEPARATOR, []);
        if (isset($_POST['removedTempFile']) && trim($_POST['removedTempFile']) != "" && file_exists($cleanedFilePath . $cleanedFileName)) {
            unlink($cleanedFilePath . $cleanedFileName);
            $this->update(array('global_value' => ''), array('global_name' => 'template_file'));
        }

        if (isset($_FILES['template_file']['name']) && $_FILES['template_file']['name'] != "") {
            $extension = strtolower(pathinfo($_FILES['template_file']['name'], PATHINFO_EXTENSION));
            $string = CommonService::generateRandomString(6) . ".";
            $tempName = "template" . $string . $extension;
            if (move_uploaded_file($_FILES["template_file"]["tmp_name"], CommonService::privateUploadFolder('template') . DIRECTORY_SEPARATOR . $tempName)) {
                $this->update(array('global_value' => $tempName), array('global_name' => 'template_file'));
            }
        }
        $out = '';
        foreach ($params as $fieldName => $fieldValue) {
            if ($fieldName != 'removedLogoImage' && $fieldName != 'web_version' && $fieldName != 'removedTempFile') {
                $result = $this->update(array('global_value' => $fieldValue), array('global_name' => $fieldName));
            } elseif ($fieldName == 'web_version') {
                foreach ($fieldValue as $ver) {
                    $out = $ver . ',' . $out;
                }
                $verVal = substr($out, 0, -1);
                $this->update(array('global_value' => $verVal), array('global_name' => 'web_version'));
            }
        }
        return $result;
    }

    public function getGlobalValue($globalName)
    {
        $value = $this->db->fetchOne('SELECT global_value FROM global_config WHERE global_name = ?', [$globalName]);
        return $value === false ? null : $value;
    }
}
