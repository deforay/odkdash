<?php

namespace Application\Service;

use Application\Model\GeographicalDivisionsTable;
use Laminas\Session\Container;
use Application\Service\CommonService;
use Laminas\Db\Sql\Sql;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FacilityService
{

    public $sm = null;
    public $adapter = null;

    public function __construct($sm)
    {
        $this->sm = $sm;
        $this->adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
    }

    public function getServiceManager()
    {
        return $this->sm;
    }

    public function addFacility($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->addFacilityDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'facility-add';
                $action = $username . ' has added a new facility ' . $params['facilityName'];
                $resourceName = 'Facility';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Facility details added successfully';
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateFacility($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->updateFacilityDetails($params);
            if ($result > 0) {
                $adapter->commit();
                //<-- Event log
                $subject = $result;
                $eventType = 'facility-update';
                $action = $username . ' has updated a facility ' . $params['facilityName'];
                $resourceName = 'Facility';
                $eventLogDb = $this->sm->get('EventLogTable');
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                //-------->
                $container = new Container('alert');
                $container->alertMsg = 'Facility details updated successfully';
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllFacilities($parameters)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $acl = $this->sm->get('AppAcl');
        return $facilityDb->fetchAllFacilities($parameters, $acl);
    }

    public function getFacility($id)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacility($id);
    }

    public function getFacilityList($val)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityList($val);
    }

    public function addEmail($params)
    {
        $result = 0;
        $container = new Container('alert');
        $auditMailDb = $this->sm->get('AuditMailTable');
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $db = $this->sm->get('SpiFormVer3Table');
        $configResult = $this->sm->get('Config');
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fromName = $configResult['admin']['name'];
            $fromEmailAddress = $configResult['admin']['emailAddress'];
            $toName = ucwords($params['facility']);
            $toEmailAddress = trim($params['emailAddress']);
            $cc = $configResult['admin']['emailAddress'];
            $subject = 'SPI-RT-CHECKLIST';
            $message = '';
            $message .= '<table border="0" cellspacing="0" cellpadding="0" style="width:100%;background-color:#FFFFFF;">';
            $message .= '<tr><td align="center">';
            $message .= '<table cellpadding="1" style="width:100%;font-family:Helvetica,Arial,sans-serif;margin:30px 0px 30px 0px;padding:2% 0% 0% 2%;background-color:#ffffff;">';
            $message .= '<tr><td>Hello ' . $params['facility'] . ',</td></tr>';
            $message .= '<tr><td><p>' . (trim($params['message'])) . '</p></td></tr>';
            $message .= '</table>';
            $message .= '</tr></td>';
            $message .= '</table>';
            $auditIds = '';
            if (isset($params['audits']) && !empty($params['audits'])) {
                $auditIds = implode(",", $params['audits']);
            }
            $mailId = $auditMailDb->insertAuditMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $auditIds);
            if ($mailId > 0) {
                $result = $facilityDb->updateFacilityEmailAddress($params);
                if ($result > 0) {
                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email") && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email")) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email");
                    }

                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId) && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId)) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId);
                    }

                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if (isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        $counter = count($_FILES['attchement']['name']);
                        for ($attch = 0; $attch < $counter; $attch++) {
                            if (trim($_FILES['attchement']['name'][$attch]) != '') {
                                $extension = strtolower(pathinfo(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = CommonService::generateRandomString(5) . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId . DIRECTORY_SEPARATOR . $fileName)) {
                                } else {
                                    $errorAttachement += 1;
                                }
                            }
                        }
                    }
                    $adapter->commit();
                    if ($errorAttachement > 0) {
                        if ($errorAttachement > 1) {
                            $container->alertMsg = $errorAttachement . 'attachements failed to upload!';
                        } else {
                            $container->alertMsg = $errorAttachement . 'attachement failed to upload!';
                        }
                    } else {
                        $container->alertMsg = 'Mail queue added successfully.';
                    }
                } else {
                    $mailId = 0;
                    $container->alertMsg = 'Error.Please try again!';
                }
            } else {
                $container->alertMsg = 'Error.Please try again!';
            }
            return $mailId;
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function addEmailV5($params)
    {
        $result = 0;
        $container = new Container('alert');
        $auditMailDb = $this->sm->get('AuditMailTable');
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $db = $this->sm->get('SpiFormVer5Table');
        $configResult = $this->sm->get('Config');
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fromName = $configResult['admin']['name'];
            $fromEmailAddress = $configResult['admin']['emailAddress'];
            $toName = ($params['facility']);
            $toEmailAddress = trim($params['emailAddress']);
            $cc = $configResult['admin']['emailAddress'];
            $subject = 'SPI-RRT-CHECKLIST - ' . $toName;
            $message = '';
            $message .= '<table border="0" cellspacing="0" cellpadding="0" style="width:100%;background-color:#FFFFFF;">';
            $message .= '<tr><td align="center">';
            $message .= '<table cellpadding="1" style="width:100%;font-family:Helvetica,Arial,sans-serif;margin:30px 0px 30px 0px;padding:2% 0% 0% 2%;background-color:#ffffff;">';
            $message .= '<tr><td>Hello ' . $params['facility'] . ',</td></tr>';
            $message .= '<tr><td><p>' . (trim($params['message'])) . '</p></td></tr>';
            $message .= '</table>';
            $message .= '</tr></td>';
            $message .= '</table>';
            $auditIds = '';
            if (isset($params['audits']) && !empty($params['audits'])) {
                $auditIds = implode(",", $params['audits']);
            }
            $mailId = $auditMailDb->insertAuditMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $auditIds);
            if ($mailId > 0) {
                $result = $facilityDb->updateFacilityEmailAddress($params);
                if ($result > 0) {
                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email") && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email")) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email");
                    }

                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId) && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId)) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId);
                    }

                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if (isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        $counter = count($_FILES['attchement']['name']);
                        for ($attch = 0; $attch < $counter; $attch++) {
                            if (trim($_FILES['attchement']['name'][$attch]) != '') {
                                $extension = strtolower(pathinfo(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = CommonService::generateRandomString(5) . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId . DIRECTORY_SEPARATOR . $fileName)) {
                                } else {
                                    $errorAttachement += 1;
                                }
                            }
                        }
                    }
                    $adapter->commit();
                    if ($errorAttachement > 0) {
                        if ($errorAttachement > 1) {
                            $container->alertMsg = $errorAttachement . 'attachements failed to upload!';
                        } else {
                            $container->alertMsg = $errorAttachement . 'attachement failed to upload!';
                        }
                    } else {
                        $container->alertMsg = 'Mail queue added successfully.';
                    }
                } else {
                    $mailId = 0;
                    $container->alertMsg = 'Error.Please try again!';
                }
            } else {
                $container->alertMsg = 'Error.Please try again!';
            }
            return $mailId;
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function addEmailV6($params)
    {
        $result = 0;
        $container = new Container('alert');
        $auditMailDb = $this->sm->get('AuditMailTable');
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $db = $this->sm->get('SpiFormVer6Table');
        $configResult = $this->sm->get('Config');
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fromName = $configResult['admin']['name'];
            $fromEmailAddress = $configResult['admin']['emailAddress'];
            $toName = $params['facility'];
            $toEmailAddress = trim($params['emailAddress']);
            $cc = $configResult['admin']['emailAddress'];
            $subject = 'SPI-RRT-CHECKLIST - ' . $toName;
            $message = '';
            $message .= '<table border="0" cellspacing="0" cellpadding="0" style="width:100%;background-color:#FFFFFF;">';
            $message .= '<tr><td align="center">';
            $message .= '<table cellpadding="1" style="width:100%;font-family:Helvetica,Arial,sans-serif;margin:30px 0px 30px 0px;padding:0;background-color:#ffffff;">';
            $message .= '<tr><td>Hello ' . $params['facility'] . ',</td></tr>';
            $message .= '<tr><td><p>' . trim($params['message']) . '</p></td></tr>';
            $message .= '</table>';
            $message .= '</tr></td>';
            $message .= '</table>';
            $auditIds = '';
            if (isset($params['audits']) && !empty($params['audits'])) {
                $auditIds = implode(",", $params['audits']);
            }
            $mailId = $auditMailDb->insertAuditMailDetails($toEmailAddress, $cc, $subject, $message, $fromName, $fromEmailAddress, $auditIds);
            if ($mailId > 0) {
                $result = $facilityDb->updateFacilityEmailAddress($params);
                if ($result > 0) {
                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email") && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email")) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email");
                    }

                    if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId) && !is_dir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId)) {
                        mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId);
                    }

                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if (isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        $counter = count($_FILES['attchement']['name']);
                        for ($attch = 0; $attch < $counter; $attch++) {
                            if (trim($_FILES['attchement']['name'][$attch]) != '') {
                                $extension = strtolower(pathinfo(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = CommonService::generateRandomString(5) . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $mailId . DIRECTORY_SEPARATOR . $fileName)) {
                                } else {
                                    $errorAttachement += 1;
                                }
                            }
                        }
                    }
                    $adapter->commit();
                    if ($errorAttachement > 0) {
                        if ($errorAttachement > 1) {
                            $container->alertMsg = $errorAttachement . 'attachements failed to upload!';
                        } else {
                            $container->alertMsg = $errorAttachement . 'attachement failed to upload!';
                        }
                    } else {
                        $container->alertMsg = 'Mail queue added successfully.';
                    }
                } else {
                    $mailId = 0;
                    $container->alertMsg = 'Error.Please try again!';
                }
            } else {
                $container->alertMsg = 'Error.Please try again!';
            }
            return $mailId;
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllTestingPoints($parameters)
    {
        $sbiFormDb = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $sbiFormDb->fetchAllTestingPointsBasedOnFacility($parameters, $acl);
    }

    public function getFacilityProfileByAudit($ids)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityProfileByAudit($ids);
    }

    public function getFacilityProfileByAuditV5($ids)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityProfileByAuditV5($ids);
    }

    public function getFacilityProfileByAuditV6($ids)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityProfileByAuditV6($ids);
    }

    public function getProvinceList()
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchProvinceList();
    }
    public function getDistrictList()
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchDistrictList();
    }

    public function mapProvince($params)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->mapProvince($params);
    }

    public function addFacilityBasedOnForm()
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->addFacilityBasedOnForm(802, 6);
    }
    public function exportFacility()
    {
        try {

            $queryContainer = new Container('query');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $output = [];
            $sheet = $spreadsheet->getActiveSheet();
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $sQueryStr = $sql->buildSqlString($queryContainer->exportAllFacilityQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (!empty($sResult)) {
                foreach ($sResult as $aRow) {
                    $row = [];
                    $row[] = $aRow['facility_id'];
                    $row[] = $aRow['facility_name'];
                    $row[] = $aRow['email'];
                    $row[] = $aRow['contact_person'];
                    $row[] = $aRow['province'];
                    $row[] = $aRow['district'];
                    $row[] = $aRow['latitude'];
                    $row[] = $aRow['longitude'];
                    $output[] = $row;
                }
            }
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                    'size' => 12,
                ),
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ),
                )
            );
            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    ),
                )
            );

            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('A2:B2');
            $sheet->mergeCells('A4:A5');
            $sheet->mergeCells('B4:B5');
            $sheet->mergeCells('C4:C5');
            $sheet->mergeCells('D4:D5');
            $sheet->mergeCells('E4:E5');
            $sheet->mergeCells('F4:F5');
            $sheet->mergeCells('G4:G5');
            $sheet->mergeCells('H4:H5');

            $sheet->setCellValue('A1', html_entity_decode('Facility Report', ENT_QUOTES, 'UTF-8'));

            $sheet->setCellValue('A4', html_entity_decode('Facility Id', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('B4', html_entity_decode('Facility Name', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('C4', html_entity_decode('Email', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('D4', html_entity_decode('Contact Person', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('E4', html_entity_decode('Province', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('F4', html_entity_decode('District', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('G4', html_entity_decode('Latitude', ENT_QUOTES, 'UTF-8'));
            $sheet->setCellValue('H4', html_entity_decode('Longitude', ENT_QUOTES, 'UTF-8'));

            $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);

            $sheet->getStyle('A4:A5')->applyFromArray($styleArray);
            $sheet->getStyle('B4:B5')->applyFromArray($styleArray);
            $sheet->getStyle('C4:C5')->applyFromArray($styleArray);
            $sheet->getStyle('D4:D5')->applyFromArray($styleArray);
            $sheet->getStyle('E4:E5')->applyFromArray($styleArray);
            $sheet->getStyle('F4:F5')->applyFromArray($styleArray);
            $sheet->getStyle('G4:G5')->applyFromArray($styleArray);
            $sheet->getStyle('H4:H5')->applyFromArray($styleArray);

            $start = 0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 1;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }

            $filename = 'facility-list-report-' . date('d-M-Y-H-i-s') . '.xlsx';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        } catch (\Exception $exc) {
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());

            return "";
        }
    }
    public function getProvinceData($searchStr)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fecthProvinceData($searchStr);
    }
    public function getDistrictData($searchStr)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fecthDistrictData($searchStr);
    }

    public function getFacilityDetails($params)
    {
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        return $facilityDb->fetchFacilityDetails($params);
    }

    public function uploadFacility($params)
    {
        try {
            $container = new Container('alert');
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $uploadOption = $params['uploadOption'];

            if (isset($_FILES['facilitiesInfo']['name']) && !empty($_FILES['facilitiesInfo']['name'])) {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facilities") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facilities")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "facilities");
                }

                $extension = strtolower(pathinfo(UPLOAD_PATH . DIRECTORY_SEPARATOR . $_FILES['facilitiesInfo']['name'], PATHINFO_EXTENSION));
                $fileName = CommonService::generateRandomString(5) . "." . $extension;

                if (move_uploaded_file($_FILES["facilitiesInfo"]["tmp_name"], UPLOAD_PATH . DIRECTORY_SEPARATOR . "facilities" . DIRECTORY_SEPARATOR . $fileName)) {

                    $uploadedFilePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "facilities" . DIRECTORY_SEPARATOR . $fileName;
                    $spreadsheet = IOFactory::load($uploadedFilePath);
                    $sheetData = $spreadsheet->getActiveSheet();

                    $dataArray = $sheetData->toArray(null, true, true, true);
                    $resultArray = array_slice($dataArray, 1);
                    $filteredArray = array_filter($resultArray, function ($row) {
                        return !empty(array_filter($row)); // Remove empty rows
                    });
                    $total = count($filteredArray);
                    $facilityNotAdded = [];

                    if ($total == 0) {
                        $container->alertMsg = 'Please enter all the mandatory fields in the excel sheet';
                        return "";
                    }

                    foreach ($filteredArray as $rowIndex => $rowData) {
                        if (empty($rowData['A']) || empty($rowData['B'])) {
                            $container->alertMsg = 'Please enter all the mandatory fields in the excel sheet';
                            return "";
                        }
                        $geographicalDivisionsTable = $this->sm->get('GeographicalDivisionsTable');

                        $provinceId = $geographicalDivisionsTable->addProvinceByFacility(trim($rowData['E']));
                        $districtId = $geographicalDivisionsTable->addDistrictByFacility($provinceId, trim($rowData['F']));
                        $facilityIdCheck = CommonService::getDataFromOneFieldAndValue('spi_rt_3_facilities', 'facility_id', $rowData['A'], $this->sm);
                        $facilityCheck = CommonService::getDataFromOneFieldAndValue('spi_rt_3_facilities', 'facility_name', $rowData['B'], $this->sm);
                        //  print_r($facilityCheck); echo "___"; print_r($facilityCheck); die;
                        $data = [
                            'facility_id' => trim($rowData['A']) ?? null,
                            'facility_name' => trim($rowData['B']) ?? null,
                            'email' => trim($rowData['C']) ?? null,
                            'contact_person' => trim($rowData['D']) ?? null,
                            'province' => $provinceId,
                            'district' => $districtId,
                            'latitude' => trim($rowData['G']) ?? null,
                            'longitude' => trim($rowData['H']) ?? null,
                            'status' => 'active'
                        ];
                        try {
                            if ($uploadOption == "facility_name_match") {
                                if (!empty($facilityCheck)) {
                                    $facilityDb->update($data, array('id' => $facilityCheck['id']));
                                } else {
                                    $facilityDb->insert($data);
                                    //$facilityNotAdded[] = $rowData;
                                }
                            } elseif ($uploadOption == "facility_id_match") {
                                if (!empty($facilityIdCheck)) {
                                    $facilityDb->update($data, array('id' => $facilityIdCheck['id']));
                                } else {
                                    $facilityDb->insert($data);
                                    //$facilityNotAdded[] = $rowData;
                                }
                            } elseif ($uploadOption == "skip_facility_name_code") {
                                if (empty($facilityIdCheck) || empty($facilityCheck)) {
                                    $facilityDb->insert($data);
                                } else {
                                    $facilityNotAdded[] = $rowData;
                                }
                            } elseif ($uploadOption == "update_duplicate_facility_name_code") {
                                if (!empty($facilityIdCheck) || !empty($facilityCheck)) {
                                    if (!empty($facilityCheck)) {
                                        $facilityDb->update($data, array('id' => $facilityCheck['id']));
                                    }
                                    if (!empty($facilityIdCheck)) {
                                        $facilityDb->update($data, array('id' => $facilityIdCheck['id']));
                                    }
                                } else {
                                    $facilityDb->insert($data);
                                    //$facilityNotAdded[] = $rowData;
                                }
                            } elseif ($uploadOption == "facility_name_id_match") {
                                if (!empty($facilityIdCheck) && !empty($facilityCheck)) {
                                    $facilityDb->update($data, array('id' => $facilityCheck['id']));
                                } else {
                                    $facilityDb->insert($data);
                                    //$facilityNotAdded[] = $rowData;
                                }
                            } else {
                                if (empty($facilityIdCheck) && empty($facilityCheck)) {
                                    $facilityDb->insert($data);
                                } else {
                                    $facilityNotAdded[] = $rowData;
                                }
                            }
                        } catch (Throwable $e) {
                            $facilityNotAdded[] = $rowData;
                            error_log($db->getLastError());
                            error_log($db->getLastQuery());
                        }
                    }

                    $notAdded = count($facilityNotAdded);
                    if ($notAdded > 0) {
                        $spreadsheet = IOFactory::load(WEB_ROOT . '/files/facilities/Facilities_Bulk_Upload_Excel_Format.xlsx');
                        $sheet = $spreadsheet->getActiveSheet();
                        foreach ($facilityNotAdded as $rowNo => $dataValue) {
                            $rRowCount = $rowNo + 2;
                            $sheet->fromArray($dataValue, null, 'A' . $rRowCount);
                        }
                        $writer = IOFactory::createWriter($spreadsheet, IOFactory::READER_XLSX);
                        $filename = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . 'INCORRECT-FACILITY-ROWS.xlsx';
                        $writer->save($filename);
                    }

                    $container->alertMsg = 'Facilities added successfully';
                } else {
                    $container->alertMsg = 'Bulk Facility Import Failed';
                    return "";
                }
            }
            $result = [];
            $result['total'] = $total;
            $result['notAdded'] = $notAdded;
            $result['option'] = $uploadOption;
            return $result;
        } catch (\Exception $exc) {
            error_log("UPLOAD-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
    }
}
