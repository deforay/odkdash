<?php

namespace Application\Service;

use Application\Session\Container;
use Application\Service\CommonService;
use Doctrine\DBAL\Connection;
use App\Export\FacilityReport;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FacilityService
{

    public $sm = null;

    public function __construct($sm)
    {
        $this->sm = $sm;
    }

    public function addFacility($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->addFacilityDetails($params);
            if ($result > 0) {
                $db->commit();
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
            $db->rollBack();
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function updateFacility($params)
    {
        $loginContainer = new Container('credo');
        $username = $loginContainer->login;
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $result = $facilityDb->updateFacilityDetails($params);
            if ($result > 0) {
                $db->commit();
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
            $db->rollBack();
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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

    public function addEmail($params)
    {
        $result = 0;
        $container = new Container('alert');
        $auditMailDb = $this->sm->get('AuditMailTable');
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $configResult = $this->sm->get('Config');
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
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
                    $attachmentFolder = CommonService::tempFolder('audit-email' . DIRECTORY_SEPARATOR . (int) $mailId);

                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if (isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        $counter = count($_FILES['attchement']['name']);
                        for ($attch = 0; $attch < $counter; $attch++) {
                            if (trim($_FILES['attchement']['name'][$attch]) != '') {
                                $extension = strtolower(pathinfo($_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = CommonService::generateRandomString(5) . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], $attachmentFolder . DIRECTORY_SEPARATOR . $fileName)) {
                                } else {
                                    $errorAttachement += 1;
                                }
                            }
                        }
                    }
                    $db->commit();
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
                    $db->rollBack();
                    $mailId = 0;
                    $container->alertMsg = 'Error.Please try again!';
                }
            } else {
                $db->rollBack();
                $container->alertMsg = 'Error.Please try again!';
            }
            return $mailId;
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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
        $configResult = $this->sm->get('Config');
        $db = $this->sm->get(Connection::class);
        $db->beginTransaction();
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
                    $attachmentFolder = CommonService::tempFolder('audit-email' . DIRECTORY_SEPARATOR . (int) $mailId);

                    //Move Attachement File(s)
                    $errorAttachement = 0;
                    if (isset($_FILES['attchement']['name']) && count($_FILES['attchement']['name']) > 0) {
                        $counter = count($_FILES['attchement']['name']);
                        for ($attch = 0; $attch < $counter; $attch++) {
                            if (trim($_FILES['attchement']['name'][$attch]) != '') {
                                $extension = strtolower(pathinfo($_FILES['attchement']['name'][$attch], PATHINFO_EXTENSION));
                                $fileName = CommonService::generateRandomString(5) . "." . $extension;
                                if (move_uploaded_file($_FILES["attchement"]["tmp_name"][$attch], $attachmentFolder . DIRECTORY_SEPARATOR . $fileName)) {
                                } else {
                                    $errorAttachement += 1;
                                }
                            }
                        }
                    }
                    $db->commit();
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
                    $db->rollBack();
                    $mailId = 0;
                    $container->alertMsg = 'Error.Please try again!';
                }
            } else {
                $db->rollBack();
                $container->alertMsg = 'Error.Please try again!';
            }
            return $mailId;
        } catch (\Exception $exc) {
            if ($db->isTransactionActive()) {
                $db->rollBack();
            }
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
        // The id names an audit, so the user must be able to reach that audit.
        if (trim((string) $ids) !== '' && !$this->sm->get('SpiFormVer3Table')->auditInScope(base64_decode((string) $ids))) {
            return [];
        }
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $result = $facilityDb->fetchFacilityProfileByAudit($ids);
        // The facility's audits are matched by name, and another location
        // can have a facility of the same name, so each one is checked too.
        if (!empty($result['auditsResult'])) {
            $audits = $this->sm->get('SpiFormVer3Table');
            $result['auditsResult'] = array_values(array_filter(
                $result['auditsResult'],
                static fn(array $audit): bool => $audits->auditInScope($audit['id'])
            ));
        }
        return $result;
    }

    public function getFacilityProfileByAuditV6($ids)
    {
        // The id names an audit, so the user must be able to reach that audit.
        if (trim((string) $ids) !== '' && !$this->sm->get('SpiFormVer6Table')->auditInScope(base64_decode((string) $ids))) {
            return [];
        }
        $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
        $result = $facilityDb->fetchFacilityProfileByAuditV6($ids);
        // The facility's audits are matched by name, and another location
        // can have a facility of the same name, so each one is checked too.
        if (!empty($result['auditsResult'])) {
            $audits = $this->sm->get('SpiFormVer6Table');
            $result['auditsResult'] = array_values(array_filter(
                $result['auditsResult'],
                static fn(array $audit): bool => $audits->auditInScope($audit['id'])
            ));
        }
        return $result;
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
            // The query the facility list last ran, without its page. A session
            // that has not opened the list since this changed holds none.
            $query = $queryContainer->exportAllFacilityQuery;
            $rows = (function () use ($query): \Generator {
                if (!is_array($query)) {
                    return;
                }
                foreach ($this->sm->get(Connection::class)->iterateAssociative($query['sql'], $query['values']) as $aRow) {
                    yield [
                        $aRow['facility_id'],
                        $aRow['facility_name'],
                        $aRow['email'],
                        $aRow['contact_person'],
                        $aRow['province'],
                        $aRow['district'],
                        $aRow['latitude'],
                        $aRow['longitude'],
                    ];
                }
            })();
            $filename = 'facility-list-report-' . date('d-M-Y-H-i-s') . '.xlsx';
            FacilityReport::write(CommonService::downloadFile($filename), 'Facility Report', '', ['Facility Id', 'Facility Name', 'Email', 'Contact Person', 'Province', 'District', 'Latitude', 'Longitude'], $rows);
            return $filename;
        } catch (\Exception $exc) {
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
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
            $total = 0;
            $notAdded = 0;

            if (isset($_FILES['facilitiesInfo']['name']) && !empty($_FILES['facilitiesInfo']['name'])) {
                $facilitiesFolder = CommonService::privateUploadFolder('facilities');

                $extension = strtolower(pathinfo($_FILES['facilitiesInfo']['name'], PATHINFO_EXTENSION));
                $fileName = CommonService::generateRandomString(5) . "." . $extension;

                if (move_uploaded_file($_FILES["facilitiesInfo"]["tmp_name"], $facilitiesFolder . DIRECTORY_SEPARATOR . $fileName)) {

                    $uploadedFilePath = $facilitiesFolder . DIRECTORY_SEPARATOR . $fileName;
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
                        } catch (\Throwable $e) {
                            $facilityNotAdded[] = $rowData;
                            error_log($e->getMessage());
                            error_log($e->getTraceAsString());
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
                        $incorrectRowsFile = 'INCORRECT-FACILITY-ROWS-' . date('d-M-Y-H-i-s') . '.xlsx';
                        $writer->save(CommonService::downloadFile($incorrectRowsFile));
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
            $result['file'] = $incorrectRowsFile ?? '';
            return $result;
        } catch (\Exception $exc) {
            error_log("UPLOAD-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
    }
}
