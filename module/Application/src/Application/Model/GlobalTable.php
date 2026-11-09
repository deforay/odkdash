<?php

namespace Application\Model;

use App\Db\DataTables;
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

        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name'] != "") {
            if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo");
            }
            $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['logo']['name'], PATHINFO_EXTENSION));
            $string = CommonService::generateRandomString(6) . ".";
            $imageName = "logo" . $string . $extension;
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName)) {
                $this->update(array('global_value' => $imageName), array('global_name' => 'logo'));
            }
        }

        $fileName =$_POST['removedAdditionalLogoImage'];
        $cleanedFileName = CommonService::cleanFileName($fileName);

        if (isset($_POST['removedAdditionalLogoImage']) && trim($_POST['removedAdditionalLogoImage']) != "" && file_exists($cleanedFilePath.DIRECTORY_SEPARATOR.$cleanedFileName)) {
            unlink($cleanedFilePath.DIRECTORY_SEPARATOR.$cleanedFileName);
            $this->update(array('global_value' => ''), array('global_name' => 'additional_logo'));
        }

        if (isset($_FILES['additional_logo']['name']) && $_FILES['additional_logo']['name'] != "") {

            if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo");
            }
            $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['additional_logo']['name'], PATHINFO_EXTENSION));
            $string = CommonService::generateRandomString(6) . ".";
            $imageName = "additional_logo" . $string . $extension;
            if (move_uploaded_file($_FILES["additional_logo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "logo" . DIRECTORY_SEPARATOR . $imageName)) {
                $this->update(array('global_value' => $imageName), array('global_name' => 'additional_logo'));
            }
        }

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
