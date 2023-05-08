<?php

namespace Application\Service;

use ZipArchive;
use CpChart\Data;
use CpChart\Image;
use CpChart\Chart\Pie;
use Laminas\Db\Sql\Sql;
use CpChart\Chart\Radar;
use Shuchkin\SimpleXLSXGen;
use Laminas\Filter\Exception;
use Laminas\Session\Container;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use GuzzleHttp\Client;

class OdkFormService
{

    public $sm = null;
    public $translator = null;
    public $adapter = null;

    public function __construct($sm)
    {
        $this->sm = $sm;
        $this->translator = $sm->get('translator');
        $this->adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
    }

    public function getServiceManager()
    {
        return $this->sm;
    }

    public function saveSpiFormVer3($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->saveData($params);
    }

    public function saveSpiFormVer5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->saveData($params);
    }

    public function saveSpiFormVer6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->saveData($params);
    }

    public function getPerformance($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformance($params);
    }

    public function getPerformanceLast30Days($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast30Days($params);
    }

    public function getPerformanceLast180Days($params = null)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getPerformanceLast180Days();
    }

    public function getAllSubmissions($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllSubmissions($sortOrder);
    }
    //export all submissions
    public function exportAllSubmissions($params)
    {
        try {
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            // $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            // $excel = new \PHPExcel();
            // $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            // $cacheSettings = array('memoryCacheSize' => '1024MB');
            // \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $outputScore = array();
            $sheet = $spreadsheet->getActiveSheet();
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($this->adapter);
            $displayDate = "";
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date Range : " . $fromDate;
                } else {
                    $displayDate = "Date Range : " . $fromDate . " to " . $toDate;
                }
            }
            $auditRndNo = '';
            $levelData = '';
            $affiliation = '';
            $province = '';
            $scoreLevel = '';
            $testPoint = '';
            if (isset($params['auditRndNo']) && ($params['auditRndNo'] != "")) {
                $auditRndNo = "Audit Round No. : " . $params['auditRndNo'];
            }
            if (isset($params['level']) && ($params['level'] != "")) {
                $levelData = "Level : " . $params['level'];
            }
            if (isset($params['affiliation']) && ($params['affiliation'] != "")) {
                $affiliation = "Affiliation : " . $params['affiliation'];
            }
            if (isset($params['province']) && ($params['province'] != "")) {
                $province = "Province/District(s) : " . implode(',', $params['province']);
            }
            if (isset($params['scoreLevel']) && ($params['scoreLevel'] != "")) {
                $scoreLevel = "Score Level : " . $params['scoreLevel'];
            }
            if (isset($params['testPoint']) && ($params['testPoint'] != "")) {
                $testPoint = "Type of Testing Point : " . $params['testPoint'];
            }

            $sQueryStr = $sql->buildSqlString($queryContainer->exportAllDataQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (count($sResult) > 0) {
                $auditScore = 0;
                $levelZero = array();
                $levelOne = array();
                $levelTwo = array();
                $levelThree = array();
                $levelFour = array();
                for ($l = 0; $l < count($sResult); $l++) {
                    $row = array();
                    foreach ($sResult[$l] as $key => $aRow) {
                        if ($key != 'id' && $key != 'content' && $key != 'token') {

                            if ($key == 'AUDIT_SCORE_PERCANTAGE' || $key == 'AUDIT_SCORE_PERCENTAGE') {
                                if (!isset($sResult[$l][$key]) || !is_numeric($sResult[$l][$key])) {
                                    continue;
                                }

                                $auditScore += $sResult[$l][$key];
                                if ($sResult[$l][$key] < 40) {
                                    $levelZero[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 40 && $sResult[$l][$key] < 60) {
                                    $levelOne[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 60 && $sResult[$l][$key] < 80) {
                                    $levelTwo[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 80 && $sResult[$l][$key] < 90) {
                                    $levelThree[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 90) {
                                    $levelFour[] = $sResult[$l][$key];
                                }
                            }
                            if ($key == 'level_other') {
                                $level = " - " . $sResult[$l][$key];
                            } else {
                                $level = '';
                            }
                            if ($key == 'today') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            } else if ($key == 'assesmentofaudit') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            }
                            $row[] = $sResult[$l][$key] . $level;
                        }
                    }
                    $output[] = $row;
                }

                $outputScore['avgAuditScore'] = (count($sResult) > 0) ? round($auditScore / count($sResult), 2) : 0;
                $outputScore['levelZeroCount'] = count($levelZero);
                $outputScore['levelOneCount'] = count($levelOne);
                $outputScore['levelTwoCount'] = count($levelTwo);
                $outputScore['levelThreeCount'] = count($levelThree);
                $outputScore['levelFourCount'] = count($levelFour);
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
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ),
                ),
            );
            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    ),
                ),
            );

            $sheet->mergeCells('A1:B1');
            $sheet->mergeCells('A2:B2');
            $sheet->mergeCells('C2:D2');
            $sheet->mergeCells('E2:F2');
            $sheet->mergeCells('G2:H2');
            $sheet->mergeCells('I2:J2');
            $sheet->mergeCells('K2:L2');
            $sheet->mergeCells('M2:N2');
            $sheet->mergeCells('A4:A5');
            $sheet->mergeCells('B4:B5');
            $sheet->mergeCells('C4:C5');
            $sheet->mergeCells('D4:D5');
            $sheet->mergeCells('E4:E5');
            $sheet->mergeCells('F4:F5');
            $sheet->mergeCells('G4:G5');
            $sheet->mergeCells('H4:H5');
            $sheet->mergeCells('I4:I5');

            $sheet->setCellValue('A1', html_entity_decode('Facility Report SPI-RT--CHECKLIST-version-3', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C2', html_entity_decode($auditRndNo, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E2', html_entity_decode($levelData, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G2', html_entity_decode($affiliation, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I2', html_entity_decode($province, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('K2', html_entity_decode($scoreLevel, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M2', html_entity_decode($testPoint, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $colmnNo = 0;
            $rowmnNo = 4;
            $rowmnNo1 = 5;
            foreach ($sResult[0] as $key => $aRow) {
                if ($key != 'id' && $key != 'content' && $key != 'token') {
                    $cellName = $sheet->getCellByColumnAndRow($colmnNo, $rowmnNo)->getColumn();
                    $sheet->mergeCells($cellName . $rowmnNo . ':' . $cellName . $rowmnNo1);
                    $sheet->setCellValue($cellName . $rowmnNo, html_entity_decode($key, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->getStyle($cellName . $rowmnNo . ':' . $cellName . $rowmnNo1)->applyFromArray($styleArray);
                    $colmnNo++;
                }
            }
            $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('C2:D2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('E2:F2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('G2:H2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('I2:J2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('K2:L2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('M2:N2')->getFont()->setBold(true)->setSize(13);

            $start = 0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 0;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }
            $rCount = $rRowCount + 3;

            $sheet->setCellValue('A' . $rCount, html_entity_decode('No.of Audit(s) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $rCount, html_entity_decode(count($sResult) . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('A' . $rCount . ':B' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->setCellValue('C' . $rCount, html_entity_decode('Avg. Audit Score : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $rCount, html_entity_decode($outputScore['avgAuditScore'] . " %", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('C' . $rCount . ':D' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('E' . $rCount)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');
            $sheet->setCellValue('E' . $rCount, html_entity_decode('Level 0(Below 40) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F' . $rCount, html_entity_decode($outputScore['levelZeroCount'] . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('E' . $rCount . ':F' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('G' . $rCount)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF808000');
            $sheet->setCellValue('G' . $rCount, html_entity_decode('Level 1(40-59) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H' . $rCount, html_entity_decode($outputScore['levelOneCount'] . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('G' . $rCount . ':H' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('I' . $rCount)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF00');
            $sheet->setCellValue('I' . $rCount, html_entity_decode('Level 2(60-79) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J' . $rCount, html_entity_decode($outputScore['levelTwoCount'] . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('I' . $rCount . ':J' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('K' . $rCount)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF00FF00');
            $sheet->setCellValue('K' . $rCount, html_entity_decode('Level 3(80-89) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L' . $rCount, html_entity_decode($outputScore['levelThreeCount'] . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('K' . $rCount . ':L' . $rCount)->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('M' . $rCount)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF008000');
            $sheet->setCellValue('M' . $rCount, html_entity_decode('Level 4(90) : ', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('N' . $rCount, html_entity_decode($outputScore['levelFourCount'] . " ", ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('M' . $rCount . ':N' . $rCount)->getFont()->setBold(true)->setSize(13);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'SPI-RT--CHECKLIST-version-3-' . time() . '.xlsx';
            // print_r(($filename));die;
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        } catch (\Exception $exc) {
            error_log("SPI-RT--CHECKLIST-version-3-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());

            return "";
        }
    }

    public function exportAllV5Submissions($params)
    {
        // var_dump($params);die;
        try {
            $writer = WriterEntityFactory::createXLSXWriter();
            $customTempFolderPath = TEMP_UPLOAD_PATH;
            $filename = 'SPI-RRT--CHECKLIST-version-5-' . time() . '.xlsx';
            $TemporaryFolderPath = $customTempFolderPath . DIRECTORY_SEPARATOR . $filename;
            $writer->setTempFolder($customTempFolderPath);
            $writer->openToFile($TemporaryFolderPath);
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($this->adapter);
            $displayDate = "";
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date Range : " . $fromDate;
                } else {
                    $displayDate = "Date Range : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "Date Range : ";
            }
            $auditRndNo = '';
            $levelData = '';
            $affiliation = '';
            $province = '';
            $scoreLevel = '';
            $testPoint = '';
            if (isset($params['auditRndNo']) && ($params['auditRndNo'] != "")) {
                $auditRndNo = "Audit Round No. : " . $params['auditRndNo'];
            } else {
                $auditRndNo = "Audit Round No. : ";
            }
            if (isset($params['level']) && ($params['level'] != "")) {
                $levelData = "Level : " . $params['level'];
            } else {
                $levelData = "Level : ";
            }
            if (isset($params['affiliation']) && ($params['affiliation'] != "")) {
                $affiliation = "Affiliation : " . $params['affiliation'];
            } else {
                $affiliation = "Affiliation : ";
            }
            if (isset($params['province']) && ($params['province'] != "")) {
                $province = "Province/District(s) : " . implode(',', $params['province']);
            }
            if (isset($params['scoreLevel']) && ($params['scoreLevel'] != "")) {
                $scoreLevel = "Score Level : " . $params['scoreLevel'];
            } else {
                $scoreLevel = "Score Level : ";
            }
            if (isset($params['testPoint']) && ($params['testPoint'] != "")) {
                $testPoint = "Type of Testing Point : " . $params['testPoint'];
            } else {
                $testPoint = "Type of Testing Point : ";
            }
            $sQueryStr = $sql->buildSqlString($queryContainer->exportAllDataQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            // print_r($sResult);die;
            if (count($sResult) > 0) {
                $auditScore = 0;
                $levelZero = array();
                $levelOne = array();
                $levelTwo = array();
                $levelThree = array();
                $levelFour = array();
                for ($l = 0; $l < count($sResult); $l++) {
                    $row = array();
                    $cells = array();
                    foreach ($sResult[$l] as $key => $aRow) {
                        if ($key != 'id' && $key != 'content' && $key != 'token') {
                            if ($key == 'AUDIT_SCORE_PERCANTAGE') {
                                if (!isset($sResult[$l][$key]) || !is_numeric($sResult[$l][$key])) {
                                    continue;
                                }

                                $auditScore += $sResult[$l][$key];
                                if ($sResult[$l][$key] < 40) {
                                    $levelZero[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 40 && $sResult[$l][$key] < 60) {
                                    $levelOne[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 60 && $sResult[$l][$key] < 80) {
                                    $levelTwo[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 80 && $sResult[$l][$key] < 90) {
                                    $levelThree[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 90) {
                                    $levelFour[] = $sResult[$l][$key];
                                }
                            }
                            if ($key == 'level_other') {
                                $level = " - " . $sResult[$l][$key];
                            } else {
                                $level = '';
                            }
                            if ($key == 'today') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            } else if ($key == 'assesmentofaudit') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            }
                            $row[] = $sResult[$l][$key] . $level;
                        }
                    }
                    $output[] = $row;
                }
                $outputScore['avgAuditScore'] = (count($sResult) > 0) ? round($auditScore / count($sResult), 2) : 0;
                $outputScore['levelZeroCount'] = count($levelZero);
                $outputScore['levelOneCount'] = count($levelOne);
                $outputScore['levelTwoCount'] = count($levelTwo);
                $outputScore['levelThreeCount'] = count($levelThree);
                $outputScore['levelFourCount'] = count($levelFour);
            }
            $fieldNames = array();
            foreach ($sResult[0] as $key => $aRow) {
                if ($key != 'id' && $key != 'content' && $key != 'token') {
                    $fieldNames[] = $key;
                }
            }
            $mainheadingstyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->build();
            $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK)
                ->setBorderTop(Color::BLACK)
                ->setBorderLeft(Color::BLACK)
                ->setBorderRight(Color::BLACK)
                ->build();
            $headingstyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBorder($border)
                ->build();
            $style = (new StyleBuilder())
                ->setBorder($border)
                ->build();
            $basicStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->build();
            $heading = ['Facility Report SPI-RRT--CHECKLIST-version-5'];
            $headingTitle = WriterEntityFactory::createRowFromArray($heading, $mainheadingstyle);
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow($headingTitle);
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([$displayDate, $auditRndNo, $levelData, $affiliation, $scoreLevel, $testPoint], $basicStyle)
            );
            $rowFromValues = WriterEntityFactory::createRowFromArray($fieldNames, $headingstyle);
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow($rowFromValues);
            foreach ($output as $rowNo => $rowData) {
                $rowValues = WriterEntityFactory::createRowFromArray($rowData, $style);
                $writer->addRow($rowValues);
            }

            $levelZeroStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor(Color::RED)
                ->build();
            $levelOneStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor(Color::rgb(128, 128, 0))
                ->build();
            $levelTwoStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor(Color::YELLOW)
                ->build();
            $levelThreeStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor(Color::rgb(0, 255, 0))
                ->build();
            $levelFourStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::BLACK)
                ->setBackgroundColor(Color::rgb(0, 128, 0))
                ->build();

            if ((int) $outputScore['avgAuditScore'] >= 0 && (int) $outputScore['avgAuditScore'] < 40) {
                $avgStyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor(Color::RED)
                    ->build();
            } else if ((int) $outputScore['avgAuditScore'] >= 40 && (int) $outputScore['avgAuditScore'] <= 59) {
                $avgStyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor(Color::rgb(128, 128, 0))
                    ->build();
            } else if ((int) $outputScore['avgAuditScore'] >= 60 && (int) $outputScore['avgAuditScore'] <= 79) {
                $avgStyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor(Color::YELLOW)
                    ->build();
            } else if ((int) $outputScore['avgAuditScore'] >= 80 && (int) $outputScore['avgAuditScore'] <= 89) {
                $avgStyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor(Color::rgb(0, 255, 0))
                    ->build();
            } else if ((int) $outputScore['avgAuditScore'] >= 90) {
                $avgStyle = (new StyleBuilder())
                    ->setFontBold()
                    ->setFontColor(Color::BLACK)
                    ->setBackgroundColor(Color::rgb(0, 128, 0))
                    ->build();
            }
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['No.of Audit(s)    : ', count($sResult)], $basicStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Avg. Audit Score    : ', $outputScore['avgAuditScore']], $avgStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray([''])
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Level 0(Below 40) : ', $outputScore['levelZeroCount']], $levelZeroStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Level 1(40-59)    : ', $outputScore['levelOneCount']], $levelOneStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Level 2(60-79)    : ', $outputScore['levelTwoCount']], $levelTwoStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Level 3(80-89)    : ', $outputScore['levelThreeCount']], $levelThreeStyle)
            );
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(['Level 4(90)       : ', $outputScore['levelFourCount']], $levelFourStyle)
            );
            $writer->close();
            return $filename;
        } catch (\Exception $exc) {
            error_log("SPI-RRT--CHECKLIST-version-5-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());

            return "";
        }
    }

    public function getAllSubmissionsDetails($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDetails($params, $acl);
    }

    public function getAllSpiV5SubmissionsDetails($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDetails($params, $acl);
    }

    public function getAllDuplicateSubmissionsDetails()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllDuplicateSubmissionsDetails();
    }

    public function getAllSubmissionsDatas($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDatas($params, $acl);
    }
    //get pending facility names
    public function getPendingFacilityNames()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchPendingFacilityNames();
    }
    //get all facility names
    public function getAllFacilityNames()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllFacilityNames();
    }
    //merge all facility name
    public function mergeFacilityName($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->mergeFacilityName($params);
    }

    public function mergeFacilityNameV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->mergeFacilityName($params);
    }

    public function getAuditRoundWiseData($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAuditRoundWiseData($params);
    }
    //download pie chart
    public function getPerformancePieChart($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $result = $db->getPerformance($params);
        $MyData = new Data();
        if (count($result) > 0) {
            foreach ($result as $key => $data) {
                $MyData->addPoints(array($data['level0'], $data['level1'], $data['level2'], $data['level3'], $data['level4']), "Level" . $key);
                $MyData->setSerieDescription("Level" . $key);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Level" . $key, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }

        $percentage = $result[0]['level0'] + $result[0]['level1'] + $result[0]['level2'] + $result[0]['level3'] + $result[0]['level4'];

        /* Define the absissa serie */
        $MyData->addPoints(
            array(
                $this->translator->translate('Level 0 (Below 40)') . "&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level0'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level0'] . ")",
                $this->translator->translate('Level 1 (40-59)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level1'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level1'] . ")",
                $this->translator->translate('Level 2 (60-79)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level2'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level2'] . ")",
                $this->translator->translate('Level 3 (80-89)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level3'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level3'] . ")",
                $this->translator->translate('Level 4 (90 and above)') . "&nbsp;" . round(($result[0]['level4'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level4'] . ")",
            ),
            "Labels"
        );
        $MyData->setAbscissa("Labels");

        /* Create the pChart object */
        $myPicture = new Image(400, 510, $MyData);
        $myPicture->drawRectangle(0, 0, 390, 480, array("R" => 0, "G" => 0, "B" => 0));
        $path = FONT_PATH . DIRECTORY_SEPARATOR;

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 13, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 0, "Y" => 0, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 0));

        $PieChart = new Pie($myPicture, $MyData);
        $PieChart->draw2DPie(195, 195, array("Radius" => 190, "Border" => true));
        $PieChart->drawPieLegend(5, 390);
        $fileName = 'piechart.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "piechart.png");
        return $fileName;
    }

    public function getSpiV5PerformancePieChart($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        $result = $db->getPerformanceV5($params);
        // echo "Prasath";die;
        $MyData = new Data();
        if (count($result) > 0) {
            foreach ($result as $key => $data) {
                $MyData->addPoints(array($data['level0'], $data['level1'], $data['level2'], $data['level3'], $data['level4']), "Level" . $key);
                $MyData->setSerieDescription("Level" . $key);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Level" . $key, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }

        $percentage = $result[0]['level0'] + $result[0]['level1'] + $result[0]['level2'] + $result[0]['level3'] + $result[0]['level4'];

        /* Define the absissa serie */
        $MyData->addPoints(
            array(
                $this->translator->translate('Level 0 (Below 40)') . "&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level0'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level0'] . ")",
                $this->translator->translate('Level 1 (40-59)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level1'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level1'] . ")",
                $this->translator->translate('Level 2 (60-79)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level2'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level2'] . ")",
                $this->translator->translate('Level 3 (80-89)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level3'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level3'] . ")",
                $this->translator->translate('Level 4 (90 and above)') . "&nbsp;" . round(($result[0]['level4'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level4'] . ")",
            ),
            "Labels"
        );
        $MyData->setAbscissa("Labels");

        /* Create the pChart object */
        $myPicture = new Image(400, 510, $MyData);
        $myPicture->drawRectangle(0, 0, 390, 480, array("R" => 0, "G" => 0, "B" => 0));
        $path = FONT_PATH . DIRECTORY_SEPARATOR;

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 13, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 0, "Y" => 0, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 0));

        $PieChart = new Pie($myPicture, $MyData);
        $PieChart->draw2DPie(195, 195, array("Radius" => 190, "Border" => true));
        $PieChart->drawPieLegend(5, 390);
        $fileName = 'piechart-spiv5.png';
        $myPicture->drawText(540, 200, "Extended AA pass / Splitted", ["R" => 0, "G" => 0, "B" => 0, "Align" => TEXT_ALIGN_TOPMIDDLE]);
        $fileName = 'piechart-spiv5.png';
        $PieChart->pChartObject->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);

        //header('Content-Type: text/plain');
        //var_dump($path);die;
        //$result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "piechart-spiv5.png");
        return $fileName;
    }

    //download spider chart pdf
    public function getAuditRoundWiseDataChart($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $result = $db->getAuditRoundWiseData($params);
        $MyData = new Data();
        /* Create and populate the pData object */
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'], 2), round($adata['PHYSICAL_SCORE'], 2), round($adata['SAFETY_SCORE'], 2), round($adata['PRETEST_SCORE'], 2), round($adata['TEST_SCORE'], 2), round($adata['POST_SCORE'], 2), round($adata['EQA_SCORE'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);
        //$myPicture->drawGradientArea(0,0,450,50,DIRECTION_VERTICAL,array("StartR"=>400,"StartG"=>400,"StartB"=>400,"EndR"=>480,"EndG"=>480,"EndB"=>480,"Alpha"=>0));
        //$myPicture->drawGradientArea(0,0,450,25,DIRECTION_HORIZONTAL,array("StartR"=>60,"StartG"=>60,"StartB"=>60,"EndR"=>200,"EndG"=>200,"EndB"=>200,"Alpha"=>0));
        //$myPicture->drawLine(0,25,450,25,array("R"=>255,"G"=>255,"B"=>255));
        //$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */
        //$myPicture->setFontProperties(array("FontName"=>$path."/Silkscreen.ttf","FontSize"=>6));
        //$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "radar.png");
        return $fileName;
    }

    public function getSpiV5AuditRoundWiseDataChart($params)
    {

        $db = $this->sm->get('SpiFormVer5Table');
        $result = $db->getAuditRoundWiseDataV5($params);
        $MyData = new Data();
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'], 2), round($adata['PHYSICAL_SCORE'], 2), round($adata['SAFETY_SCORE'], 2), round($adata['PRETEST_SCORE'], 2), round($adata['TEST_SCORE'], 2), round($adata['POST_SCORE'], 2), round($adata['EQA_SCORE'], 2), round($adata['RTRI_SCORE'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit", "RTRT Surveillance"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);
        //$myPicture->drawGradientArea(0,0,450,50,DIRECTION_VERTICAL,array("StartR"=>400,"StartG"=>400,"StartB"=>400,"EndR"=>480,"EndG"=>480,"EndB"=>480,"Alpha"=>0));
        //$myPicture->drawGradientArea(0,0,450,25,DIRECTION_HORIZONTAL,array("StartR"=>60,"StartG"=>60,"StartB"=>60,"EndR"=>200,"EndG"=>200,"EndB"=>200,"Alpha"=>0));
        //$myPicture->drawLine(0,25,450,25,array("R"=>255,"G"=>255,"B"=>255));
        //$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */
        //$myPicture->setFontProperties(array("FontName"=>$path."/Silkscreen.ttf","FontSize"=>6));
        //$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));
        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-spiv5.png';
        //print_r($fileName);die;
        $result = $myPicture->render(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    public function getFormData($id, $pdf = 'no')
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getFormData($id, $pdf);
    }

    public function getSpiV5FormData($id, $pdf = 'no')
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getFormData($id, $pdf);
    }

    public function getSpiV3FormLabels()
    {
        $db = $this->sm->get('SpiFormLabelsTable');
        return $db->getAllLabels();
    }

    public function approveFormStatus($params)
    {
        //\Zend\Debug\Debug::dump(count($params['idList']));die;
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $db = $this->sm->get('SpiFormVer3Table');
            if (isset($params['idList']) && $params['idList'] != '') {
                for ($i = 0; $i < count($params['idList']); $i++) {
                    $result = $db->updateFormStatus($params['idList'][$i], 'approved');
                    $facilityDb->addFacilityBasedOnForm($params['idList'][$i]);
                }
            }
            if (isset($params['id'])) {
                $result = $db->updateFormStatus($params['id'], 'approved');
                $facilityDb->addFacilityBasedOnForm($params['id']);
            }
            if ($result > 0) {
                $adapter->commit();
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function approveSpiV5FormStatus($params)
    {
        //\Zend\Debug\Debug::dump(count($params['idList']));die;
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            //$facilityDb = $this->sm->get('SpiRt5FacilitiesTable');
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $db = $this->sm->get('SpiFormVer5Table');
            if (isset($params['idList']) && $params['idList'] != '') {
                for ($i = 0; $i < count($params['idList']); $i++) {
                    $result = $db->updateFormStatus($params['idList'][$i], 'approved');
                    $facilityDb->addFacilityBasedOnForm($params['idList'][$i]);
                }
            }
            if (isset($params['id'])) {
                $result = $db->updateFormStatus($params['id'], 'approved');
                $facilityDb->addFacilityBasedOnForm($params['id']);
            }
            if ($result > 0) {
                $adapter->commit();
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    public function getAllApprovedSubmissions($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissions($sortOrder);
    }

    public function getAllApprovedSubmissionsTable($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fecthAllApprovedSubmissionsTable($params);
    }

    public function getAllApprovedV5FormSubmissionsTable($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllApprovedV5FormSubmissionsTable($params);
    }
    //export facilty report
    public function exportFacilityReport($params)
    {
        try {
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'facility-report-v3' . date('d-M-Y-H-i-s') . '.xlsx';
            $output = array();
            $sheet = $spreadsheet->getActiveSheet();
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($this->adapter);
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date : " . $fromDate;
                } else {
                    $displayDate = "Date : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "";
            }

            $sQueryStr = $sql->buildSqlString($queryContainer->exportQuery);

            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (count($sResult) > 0) {

                foreach ($sResult as $aRow) {
                    $auditDate = "";
                    if (isset($aRow['assesmentofaudit']) && trim($aRow['assesmentofaudit']) != "") {
                        $auditDate = \Application\Service\CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                    }
                    $row = array();
                    $row[] = $aRow['facilityname'];
                    $row[] = $auditDate;
                    $row[] = $aRow['testingpointname'] . " - " . $aRow['testingpointtype'];
                    $row[] = $aRow['PERSONAL_SCORE'];
                    $row[] = $aRow['PHYSICAL_SCORE'];
                    $row[] = $aRow['SAFETY_SCORE'];
                    $row[] = $aRow['PRETEST_SCORE'];
                    $row[] = $aRow['TEST_SCORE'];
                    $row[] = $aRow['POST_SCORE'];
                    $row[] = $aRow['EQA_SCORE'];
                    $row[] = $aRow['FINAL_AUDIT_SCORE'];
                    $row[] = round($aRow['AUDIT_SCORE_PERCANTAGE'], 2);
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
                ),
            );
            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    ),
                ),
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
            $sheet->mergeCells('I4:I5');
            $sheet->mergeCells('J4:J5');
            $sheet->mergeCells('K4:K5');
            $sheet->mergeCells('L4:L5');

            $sheet->setCellValue('A1', html_entity_decode('Facility Report Version 3', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->setCellValue('A4', html_entity_decode('Facility name', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Audit Date', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Testing Point', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Personnel Training & Certification', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Physical', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Safety', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Pre-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Post-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('External QA', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Total', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('% Scores', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(13);

            $sheet->getStyle('A4:A5')->applyFromArray($styleArray);
            $sheet->getStyle('B4:B5')->applyFromArray($styleArray);
            $sheet->getStyle('C4:C5')->applyFromArray($styleArray);
            $sheet->getStyle('D4:D5')->applyFromArray($styleArray);
            $sheet->getStyle('E4:E5')->applyFromArray($styleArray);
            $sheet->getStyle('F4:F5')->applyFromArray($styleArray);
            $sheet->getStyle('G4:G5')->applyFromArray($styleArray);
            $sheet->getStyle('H4:H5')->applyFromArray($styleArray);
            $sheet->getStyle('I4:I5')->applyFromArray($styleArray);
            $sheet->getStyle('J4:J5')->applyFromArray($styleArray);
            $sheet->getStyle('K4:K5')->applyFromArray($styleArray);
            $sheet->getStyle('L4:L5')->applyFromArray($styleArray);

            $start = 0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 1;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }

            //$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            //$filename = 'facility-report-' . date('d-M-Y-H-i-s') . '.xlsx';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        } catch (\Exception $exc) {
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
    }

    public function exportV5FacilityReport($params)
    {
        try {
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $output = array();
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'facility-report-v5' . date('d-M-Y-H-i-s') . '.xlsx';
            //$writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            $sheet = $spreadsheet->getActiveSheet();
            $sql = new Sql($this->adapter);
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date : " . $fromDate;
                } else {
                    $displayDate = "Date : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "";
            }

            $sQueryStr = $sql->buildSqlString($queryContainer->exportQuery);

            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (count($sResult) > 0) {

                foreach ($sResult as $aRow) {
                    $auditDate = "";
                    if (isset($aRow['assesmentofaudit']) && trim($aRow['assesmentofaudit']) != "") {
                        $auditDate = \Application\Service\CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                    }
                    $row = array();
                    $row[] = $aRow['facilityname'];
                    $row[] = $auditDate;
                    $row[] = $aRow['testingpointtype'];
                    $row[] = $aRow['PERSONAL_SCORE'];
                    $row[] = $aRow['PHYSICAL_SCORE'];
                    $row[] = $aRow['SAFETY_SCORE'];
                    $row[] = $aRow['PRETEST_SCORE'];
                    $row[] = $aRow['TEST_SCORE'];
                    $row[] = $aRow['POST_SCORE'];
                    $row[] = $aRow['EQA_SCORE'];
                    $row[] = $aRow['RTRI_SCORE'];
                    $row[] = $aRow['FINAL_AUDIT_SCORE'];
                    $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
                    $output[] = $row;
                }
            }
            //echo "<pre>";
            //print_r($output);die;
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
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ),
                ),
            );

            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    ),
                ),
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
            $sheet->mergeCells('I4:I5');
            $sheet->mergeCells('J4:J5');
            $sheet->mergeCells('K4:K5');
            $sheet->mergeCells('L4:L5');
            $sheet->mergeCells('M4:M5');

            $sheet->setCellValue('A1', html_entity_decode('Facility Report Version 5', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->setCellValue('A4', html_entity_decode('Facility name', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Audit Date', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Testing Point', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Personnel Training & Certification', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Physical', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Safety', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Pre-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Post-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('External QA', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('RTRI', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('Total', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('% Scores', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(13);

            $sheet->getStyle('A4:A5')->applyFromArray($styleArray);
            $sheet->getStyle('B4:B5')->applyFromArray($styleArray);
            $sheet->getStyle('C4:C5')->applyFromArray($styleArray);
            $sheet->getStyle('D4:D5')->applyFromArray($styleArray);
            $sheet->getStyle('E4:E5')->applyFromArray($styleArray);
            $sheet->getStyle('F4:F5')->applyFromArray($styleArray);
            $sheet->getStyle('G4:G5')->applyFromArray($styleArray);
            $sheet->getStyle('H4:H5')->applyFromArray($styleArray);
            $sheet->getStyle('I4:I5')->applyFromArray($styleArray);
            $sheet->getStyle('J4:J5')->applyFromArray($styleArray);
            $sheet->getStyle('K4:K5')->applyFromArray($styleArray);
            $sheet->getStyle('L4:L5')->applyFromArray($styleArray);
            $sheet->getStyle('M4:M5')->applyFromArray($styleArray);

            $start = 0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 1;
                foreach ($rowData as $field => $value) {

                    if (!isset($value)) {
                        $value = "";
                    }
                    //echo $colNo.'---'.$rowNo;die;
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    ++$colNo;
                }
            }

            //$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            //$filename = 'facility-report-' . date('d-M-Y-H-i-s') . '.xls';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        } catch (\Exception $exc) {
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
    }

    public function getAllApprovedSubmissionLocation($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllApprovedSubmissionLocation($params);
    }

    public function getZeroQuestionCounts($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getZeroQuestionCounts($params);
    }

    public function getAllApprovedTestingVolume($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getAllApprovedTestingVolume($params);
    }

    public function getSpiV3PendingCount()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->getSpiV3PendingCount();
    }

    public function getSpiV5PendingCount()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getSpiV5PendingCount();
    }

    public function updateSpiForm($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SpiFormVer3Table');
            $result = $db->updateSpiFormDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Form details updated successfully';
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    //get all audit round no
    public function getSpiV3FormAuditNo()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormAuditNo();
    }

    public function getFacilitiesAudits($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchFacilitiesAudits($params);
    }

    public function deleteAuditData($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->deleteAuditRowData($params);
    }

    public function getSpiV3FormFacilityAuditNo($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormFacilityAuditNo($params);
    }

    public function getAllApprovedSubmissionsDetailsBasedOnAuditDate($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllApprovedSubmissionsDetailsBasedOnAuditDate($params, $acl);
    }

    public function getSpiV3FormUniqueTokens()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormUniqueTokens();
    }

    public function getViewDataDetails($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchViewDataDetails($params);
    }

    public function getAllTestingPointType()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchAllTestingPointType();
    }

    public function getAllSpiV5TestingPointType()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllTestingPointTypeV5();
    }

    public function getTestingPointTypeNamesByType($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchTestingPointTypeNamesByType($params);
    }

    public function getSpiV3FormUniqueLevelNames()
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchSpiV3FormUniqueLevelNames();
    }
    public function getSpiV3FormUniqueDistrict()
    {
        $db = $this->sm->get('SpiRtFacilitiesTable');
        return $db->getSpiV3FormUniqueDistrict();
    }
    public function getDistrictData($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->fetchDistrictData($params);
    }

    public function addDownloadData($params)
    {
        $db = $this->sm->get('SpiFormVer3DownloadTable');
        return $db->addDownloadDataDetails($params);
    }

    public function addV5DownloadData($params)
    {
        $db = $this->sm->get('SpiFormVer5DownloadTable');
        return $db->addDownloadDataDetails($params);
    }

    public function getDownloadDataList()
    {
        $common = new CommonService();
        $db = $this->sm->get('SpiFormVer3DownloadTable');
        $result = $db->fetchDownloadDataList();
        if (count($result['formResult']) > 0) {
            //get config details
            $globalDb = $this->sm->get('GlobalTable');
            $configData = $globalDb->getGlobalConfig();
            $configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "label.php";
            $fileContents = file_get_contents($configFile);
            //Convert the JSON string back into an array.
            $decoded = json_decode($fileContents, true);
            $language = $configData['language'];
            foreach ($result['formResult'] as $formData) {
                // create new PDF document
                $pdf = new TcpdfExtends(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->setSchemeName(ucwords($configData['header']), $configData['logo']);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('ODK DASHBOARD');
                $pdf->SetTitle('SPI-RT Checklist');
                $pdf->SetSubject('ODK DASHBOARD');
                $pdf->SetKeywords('odk', 'odk dashboard');

                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // set some language-dependent strings (optional)
                if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                    require_once dirname(__FILE__) . '/lang/eng.php';
                    $pdf->setLanguageArray($l);
                }
                // ---------------------------------------------------------

                // set font
                $pdf->SetFont('times', '', 10);

                // add a page
                $pdf->AddPage();
                //$pdf->SetY(20,true,false);
                $partA = '<p style="font-weight:bold;line-height:24px;">' . $decoded[$language]['/SPI_RT/TESTSITE/FACILITY/info2:label'] . '</p>';
                //$partA.='<br/>';

                $pdf->writeHTML($partA, true, 0, true, 0);

                $pdf->writeHTMLCell('', 12, '', '', '<p>' . $decoded[$language]['/SPI_RT/TESTSITE/FACILITY/info2:hint'] . '</p>', 0, 1, false, true, 'L', true);
                if ($language == 'Portuguese') {
                    $langDateFormat = '(dd/mm/aaaa)';
                } else if ($language == 'Spanish') {
                    $langDateFormat = '(dd/mm/aaaa)';
                } else {
                    $langDateFormat = '(dd/mm/yyyy)';
                }
                $testingTab = '<table border="1" cellspacing="0" cellpadding="5">';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/assesmentofaudit:label'] . '</b>' . $langDateFormat . ': ' . \Application\Service\CommonService::humanReadableDateFormat($formData['assesmentofaudit']) . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/auditroundno:label'] . '</b> ' . $formData['auditroundno'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/facilityname:label'] . '</b> ' . $formData['facilityname'] . '</td>';
                if ($language == 'Portuguese') {
                    $testingTab .= '<td><b>Identificacao do local de testagem </b>(se aplicavel): ' . $formData['facilityid'] . '</td>';
                } else if ($language == 'Spanish') {
                    $testingTab .= '<td><b>Tipo de sitio de pruebas </b>(seleccione uno): ' . $formData['facilityid'] . '</td>';
                } else {
                    $testingTab .= '<td><b>Testing Facility ID</b>(if applicable) : ' . $formData['facilityid'] . '</td>';
                }
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/testingpointname:label'] . '</b> ' . $formData['testingpointname'] . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/testingpointtype:label'] . '</b> ' . $formData['testingpointtype'];
                $testingTab .= ((isset($formData['testingpointtype_other']) && $formData['testingpointtype_other'] != "") ? " - " . $formData['testingpointtype_other'] : "") . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td colspan="2"><b>' . $decoded[$language]['/SPI_RT/TESTSITE/locationaddress:label'] . '</b> ' . $formData['locationaddress'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/level:label'] . '</b> <br/>' . $formData['level'];
                $testingTab .= ((isset($formData['level_other']) && $formData['level_other'] != "") ? " Other - " . $formData['level_other'] : "") . ':' . $formData['level_name'];
                $testingTab .= '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/affiliation:label'] . '</b><br/>' . $formData['affiliation'];
                $testingTab .= ((isset($formData['affiliation_other']) && $formData['affiliation_other'] != "") ? " Other : " . $formData['affiliation_other'] : "");
                $testingTab .= '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/NumberofTester:label'] . '</b>' . $formData['NumberofTester'] . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/avgMonthTesting:label'] . '</b>' . $formData['avgMonthTesting'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/name_auditor_lead:label'] . '</b>' . $formData['name_auditor_lead'] . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/name_auditor2:label'] . '</b>' . $formData['name_auditor2'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '</table>';

                $pdf->writeHTML($testingTab, true, 0, true, 0);

                $partBHeading = '<b>' . $decoded[$language]['/SPI_RT/SPIRT/info4:label'] . '</b>';

                $pdf->writeHTML($partBHeading, true, 0, true, 0);

                $partBCont = '<br/><div>' . $decoded[$language]['/SPI_RT/SPIRT/info4:hint'] . '</div>';

                $pdf->writeHTML($partBCont, true, 0, true, 0);

                $partBTable = '<table border="1" cellspacing="0" cellpadding="5" style="width:100%;">';
                $partBTable .= "<tr>";
                $language;
                if ($language == 'Portuguese') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:52%;">SECÇÃO</td>';
                } else {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:52%;">SECTION</td>';
                }

                $partBTable .= '<td style="text-align:center;font-weight:bold;width:7%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_10/PERSONAL_Q_1_10/1:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_3/PERSONAL_Q_1_3/0.5:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:7%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_8/PERSONAL_Q_1_8/0:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:18%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_5/PERSONAL_C_1_5:label'] . '</td>';
                if ($language == 'Portuguese') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Pontuação</td>';
                } elseif ($language == 'Spanish') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Punteo</td>';
                } else {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Score</td>';
                }

                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PERSONAL:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">10</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 11; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td style="width:52%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_' . $i . '/PERSONAL_Q_1_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PERSONAL_Q_1_' . $i]) && $formData['PERSONAL_Q_1_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PERSONAL_Q_1_' . $i]) && $formData['PERSONAL_Q_1_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PERSONAL_Q_1_' . $i]) && $formData['PERSONAL_Q_1_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['PERSONAL_C_1_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['PERSONAL_Q_1_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PERSONAL/PERSONAL_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PERSONAL_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PHYSICAL:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">5</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 6; $i++) {

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/PHYSICAL/PHY_G_2_' . $i . '/PHYSICAL_Q_2_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PHYSICAL_Q_2_' . $i]) && $formData['PHYSICAL_Q_2_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PHYSICAL_Q_2_' . $i]) && $formData['PHYSICAL_Q_2_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PHYSICAL_Q_2_' . $i]) && $formData['PHYSICAL_Q_2_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['PHYSICAL_C_2_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['PHYSICAL_Q_2_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PHYSICAL/PHYSICAL_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PHYSICAL_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/SAFETY:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">11</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 12; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/SAFETY/SAF_3_' . $i . '/SAFETY_Q_3_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['SAFETY_Q_3_' . $i]) && $formData['SAFETY_Q_3_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['SAFETY_Q_3_' . $i]) && $formData['SAFETY_Q_3_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['SAFETY_Q_3_' . $i]) && $formData['SAFETY_Q_3_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['SAFETY_C_3_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['SAFETY_Q_3_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/SAFETY/SAFETY_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['SAFETY_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PRETEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">12</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 13; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/PRETEST/PRE_4_' . $i . '/PRE_Q_4_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PRE_Q_4_' . $i]) && $formData['PRE_Q_4_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PRE_Q_4_' . $i]) && $formData['PRE_Q_4_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['PRE_Q_4_' . $i]) && $formData['PRE_Q_4_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['PRE_C_4_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['PRE_Q_4_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PRETEST/PRETEST_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PRETEST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/TEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">9</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 10; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/TEST/TEST_5_' . $i . '/TEST_Q_5_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['TEST_Q_5_' . $i]) && $formData['TEST_Q_5_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['TEST_Q_5_' . $i]) && $formData['TEST_Q_5_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['TEST_Q_5_' . $i]) && $formData['TEST_Q_5_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['TEST_C_5_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['TEST_Q_5_1']) . '</td>';
                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/TEST/TEST_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['TEST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/POSTTEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">9</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 10; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/POSTTEST/POST_6_' . $i . '/POST_Q_6_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['POST_Q_6_' . $i]) && $formData['POST_Q_6_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['POST_Q_6_' . $i]) && $formData['POST_Q_6_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['POST_Q_6_' . $i]) && $formData['POST_Q_6_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['POST_C_6_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['POST_Q_6_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/POSTTEST/POST_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['POST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/EQA:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">8/14</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 9; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/EQA/EQA_7_' . $i . '/EQA_Q_7_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['EQA_C_7_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['EQA_Q_7_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="text-align:center;font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/EQA/sampleretesting:label'] . '</td>';
                $partBTable .= '</tr>';

                for ($i = 9; $i < 15; $i++) {
                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/EQA/SAMPLEREF/EQA_7_' . $i . '/EQA_Q_7_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == 1) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == 0.5) ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= (isset($formData['EQA_Q_7_' . $i]) && $formData['EQA_Q_7_' . $i] == "0") ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "";
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($formData['EQA_C_7_' . $i]) . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . ($formData['EQA_Q_7_' . $i]) . '</td>';
                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/EQA/EQA_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['EQA_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '</table>';
                if ($language == 'Portuguese') {
                    $partBTable .= '<p>*A area marcada com asteriscos so é aplicavel para os locais onde as amostras retestadas sao executadas.</p>';
                } else if ($language == 'Spanish') {
                    $partBTable .= '<p>*Lo que aparece marcado con un asterisco son solo aplicables a sitios donde la repetición de las pruebas se hace.</p>';
                } else {
                    $partBTable .= '<p>*Those marked with an asterisk are only applicable to sites where sample retesting is performed.</p>';
                }

                $pdf->writeHTML($partBTable, true, 0, true, 0);

                $partC = '<br/><p style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/scoring/info5:label'] . '</p>';
                $partC .= '<br/><span>' . $decoded[$language]['/SPI_RT/scoring/info6:label'] . '</span>';
                $partC .= '<p>' . $decoded[$language]['/SPI_RT/scoring/info10:label'] . '</p>';
                $partC .= '<p>' . $decoded[$language]['/SPI_RT/scoring/info11:label'] . '</p>';

                $pdf->writeHTML($partC, true, 0, true, 0);

                $summaryExp = explode(PHP_EOL, $decoded[$language]['/SPI_RT/SUMMARY/info17:label']);
                $totPointScored = '';
                $totExpectScored = '';
                $perScored = '';
                if (isset($summaryExp[8]) && trim($summaryExp[8]) != "") {
                    $totPointScored = $summaryExp[8];
                }
                if (isset($summaryExp[9]) && trim($summaryExp[9]) != "") {
                    $totExpectScored = $summaryExp[9];
                }
                if (isset($summaryExp[10]) && trim($summaryExp[10]) != "") {
                    $expPerScored = explode("=", $summaryExp[10]);
                    $perScored = (string) $expPerScored[0];
                }

                $partCTable = '<table border="1" cellspacing="0" cellpadding="5">';

                $partCTable .= '<tr style="font-weight:bold;text-align:center;">';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td style="width:15%">NIVEL</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td style="width:15%">Nivel</td>';
                } else {
                    $partCTable .= '<td style="width:15%">Levels</td>';
                }

                if ($language == 'Portuguese') {
                    $partCTable .= '<td  style="width:25%">PONTUACAO EM %</td>';
                    $partCTable .= '<td  style="width:60%">DESCRIÇAO DOS RESULTADOS</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td  style="width:25%">% Puntaje</td>';
                    $partCTable .= '<td  style="width:60%">Descripción de los resultados</td>';
                } else {
                    $partCTable .= '<td  style="width:25%">' . $perScored . '</td>';
                    $partCTable .= '<td  style="width:60%">Description of results</td>';
                }
                $partCTable .= '</tr>';

                $level0 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info21:label']);
                $level1 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info22:label']);
                if (count($level1) > 2) {
                    $level1[1] = $level1[1] . " - " . $level1[2];
                }
                $level2 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info23:label']);
                if (count($level2) > 2) {
                    $level2[1] = $level2[1] . " - " . $level2[2];
                }
                $level3 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info24:label']);
                if (count($level3) > 2) {
                    $level3[1] = $level3[1] . " - " . $level3[2];
                }
                $level4 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info25:label']);

                if ($language == 'Spanish') {
                    $level0[0] = "Nivel 0";
                    $level0[1] = "Menos de 40% ";
                    $level1[0] = "Nivel 1";
                    $level2[0] = "Nivel 2";
                    $level3[0] = "Nivel 3";
                    $level4[0] = "Nivel 4";
                    $level4[1] = "90% a más";
                }

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#C00000;">' . $level0[0] . '</td>';
                $partCTable .= '<td>' . $level0[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Necessidade de melhoria em todas as areas e remediaçoes imediatas</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Necesita mejorar en todas las áreas y es necesaria corrección inmediata</td>';
                } else {
                    $partCTable .= '<td>Needs improvement in all areas and immediate remediation</td>';
                }

                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#E36C0A;">' . $level1[0] . '</td>';
                $partCTable .= '<td>' . $level1[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Necessidade de melhorias em areas especificas</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Necesita mejorar en áreas específicas</td>';
                } else {
                    $partCTable .= '<td>Needs improvement in specific areas</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#FFFF00;">' . $level2[0] . '</td>';
                $partCTable .= '<td>' . $level2[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Parcialmente admissivel ou aceitavel</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Parcialmente elegible</td>';
                } else {
                    $partCTable .= '<td>Partially eligible</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#92D050;">' . $level3[0] . '</td>';
                $partCTable .= '<td>' . $level3[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Proximo da certificaçao nacional</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Cercano a sitio nacional certificado</td>';
                } else {
                    $partCTable .= '<td>Close to national site certification</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#00B050;">' . $level4[0] . '</td>';
                $partCTable .= '<td>' . $level4[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Admissivel a certificaçao nacional</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Elegible para ser certificado</td>';
                } else {
                    $partCTable .= '<td>Eligible to national site certification</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '</table>';

                $pdf->writeHTML($partCTable, true, 0, true, 0);
                $summationExp = explode(PHP_EOL, $decoded[$language]['/SPI_RT/SUMMARY/info12:label']);
                $facilityName = '';
                if (isset($summationExp[0]) && trim($summationExp[0]) != "") {
                    $heading = $summationExp[0];
                }
                if (isset($summationExp[2]) && trim($summationExp[2]) != "") {
                    $facilityName = $summationExp[2];
                }
                if (isset($summationExp[3]) && trim($summationExp[3]) != "") {
                    $auditorName = $summationExp[3];
                }
                if (isset($summationExp[4]) && trim($summationExp[4]) != "") {
                    $textPointName = $summationExp[4];
                }
                $staffAuditedName = '';
                $noOfTester = '';
                if (isset($summationExp[5]) && trim($summationExp[5]) != "") {
                    $expStaffAuditedName = explode(":", $summationExp[5]);
                    $staffAuditedName = $expStaffAuditedName[0];
                    $noOfTester = $expStaffAuditedName[1];
                }
                if ($language == 'Spanish') {
                    $heading = "PARTE D: Informe resumido del evaluador de la auditoría SPI-RT";
                }

                $partDTitle = '<p style="font-weight:bold;line-height:30px;">' . $heading . '</p>';
                $pdf->writeHTML($partDTitle, true, 0, true, 0);

                $partDtableBox1 = '<table cellspacing="0" cellpadding="2">';
                $partDtableBox1 .= "<tr><td>" . $facilityName . $formData['facilityname'] . "</td></tr>";

                $partDtableBox1 .= "<tr><td>";
                if ($language == 'Portuguese') {
                    $partDtableBox1 .= "Tipo de local:";
                } else if ($language == 'Spanish') {
                    $partDtableBox1 .= "Tipo de sitio:";
                } else {
                    $partDtableBox1 .= "Site Type:";
                }
                $partDtableBox1 .= $formData['testingpointtype'];

                $partDtableBox1 .= ((isset($formData['testingpointtype_other']) && $formData['testingpointtype_other'] != "") ? " - " . $formData['testingpointtype_other'] : "");
                $partDtableBox1 .= "</td></tr>";
                $partDtableBox1 .= "<tr><td>" . $staffAuditedName . ":" . $formData['staffaudited'] . "</td></tr>";
                $partDtableBox1 .= '</table>';
                $pdf->writeHTMLCell(50, 26, '', '', $partDtableBox1, 1, 0, 0, true, 'L');

                $partDtableBox2 = '<table cellspacing="0" cellpadding="5">';
                $partDtableBox2 .= "<tr><td>" . $noOfTester . ": " . $formData['NumberofTester'] . "</td></tr><tr><td>" . $decoded[$language]['/SPI_RT/durationaudit:label'] . $formData['durationaudit'] . "</td></tr>";
                $partDtableBox2 .= '</table>';

                $pdf->writeHTMLCell(50, 26, 70, '', $partDtableBox2, 1, 0, 0, true, 'L', true);

                $scorePer = round($formData['AUDIT_SCORE_PERCANTAGE']);
                $level = '';
                $colorCode = '';
                if ($scorePer < 40) {
                    $level = $level0[0];
                    //$level="Level 0";
                    $colorCode = "background-color:#C00000";
                } elseif ($scorePer >= 40 && $scorePer <= 59) {
                    //$level="Level 1";
                    $level = $level1[0];
                    $colorCode = "background-color:#E36C0A";
                } elseif ($scorePer >= 60 && $scorePer <= 79) {
                    //$level="Level 2";
                    $level = $level2[0];
                    $colorCode = "background-color:#FFFF00";
                } elseif ($scorePer >= 80 && $scorePer <= 89) {
                    //$level="Level 3";
                    $level = $level3[0];
                    $colorCode = "background-color:#92D050";
                } elseif ($scorePer >= 90) {
                    //$level="Level 4";
                    $level = $level4[0];
                    $colorCode = "background-color:#00B050";
                }

                $partDtableBox3 = '<table cellspacing="0" cellpadding="5">';
                $partDtableBox3 .= "<tr><td>" . $totPointScored . $formData['FINAL_AUDIT_SCORE'] . "</td></tr>";
                $partDtableBox3 .= "<tr><td>" . $totExpectScored . $formData['MAX_AUDIT_SCORE'] . "</td></tr>";
                $partDtableBox3 .= '<tr><td>' . $perScored . "= " . round($formData['AUDIT_SCORE_PERCANTAGE'], 2) . '% &nbsp; <span style="' . $colorCode . '">  &nbsp;&nbsp;' . $level . '  &nbsp;&nbsp;</span></td></tr>';
                $partDtableBox3 .= '</table>';

                $pdf->writeHTMLCell(70, 26, 125, '', $partDtableBox3, 1, 1, 0, true, 'L', true);

                // set recommend
                $recommend = explode("-", $decoded[$language]['/SPI_RT/correctiveaction/action:label']);
                $timeLine = explode("-", $decoded[$language]['/SPI_RT/correctiveaction/timeline:label']);

                $partDTable = '<br/><br/><table border="1" cellspacing="0" cellpadding="5" style="width:100%">';
                $partDTable .= '<tr>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:9%"><br/><br/>' . $decoded[$language]['/SPI_RT/correctiveaction/sectionno:label'] . '</td>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:22%"><br/><br/>' . $decoded[$language]['/SPI_RT/correctiveaction/deficiency:label'] . '</td>';
                $partDTable .= '<td colspan="2" style="font-weight:bold;text-align:center;width:20%">' . $decoded[$language]['/SPI_RT/correctiveaction/correction:label'] . '</td>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:23%">' . $decoded[$language]['/SPI_RT/correctiveaction/auditorcomment:label'] . '</td>';
                $partDTable .= '<td colspan="2" style="font-weight:bold;text-align:center;width:26%">' . $recommend[0] . '</td>';
                $partDTable .= '</tr>';
                $partDTable .= '<tr>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;width:12%;">' . $decoded[$language]['/SPI_RT/correctiveaction/correction/Immediate:label'] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;width:8%;">' . $decoded[$language]['/SPI_RT/correctiveaction/correction/Followup:label'] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;">' . $recommend[1] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;">' . $timeLine[1] . '</td>';
                $partDTable .= '</tr>';
                if (isset($formData['correctiveaction']) && $formData['correctiveaction'] != "" && $formData['correctiveaction'] != "[]") {
                    $correctiveActions = json_decode($formData['correctiveaction'], true);
                    foreach ($correctiveActions as $ca) {
                        $partDTable .= '<tr>';
                        $partDTable .= '<td style="text-align:center;">' . $ca['sectionno'] . '</td>';
                        $partDTable .= '<td>' . $ca['deficiency'] . '</td>';
                        $partDTable .= '<td style="text-align:center;">';
                        $partDTable .= ($ca['correction'] == 'Immediate' ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "");
                        $partDTable .= '</td>';
                        $partDTable .= '<td style="text-align:center;">';
                        $partDTable .= ($ca['correction'] == 'Followup' ? '<img src="' . APPLICATION_PATH . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'black-tick.png" width="20">' : "");
                        $partDTable .= '</td>';
                        $partDTable .= '<td>' . $ca['auditorcomment'] . '</td>';
                        $partDTable .= '<td>' . $ca['action'] . '</td>';
                        $partDTable .= '<td>' . $ca['timeline'] . '</td>';
                        $partDTable .= '</tr>';
                    }
                } else {
                    $partDTable .= '<tr>';
                    $partDTable .= '<td colspan="7">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_8/PERSONAL_Q_1_8/0:label'] . ' ' . $decoded[$language]['SPI_RT/correctiveaction:label'] . '</td>';
                    $partDTable .= '</tr>';
                }
                $partDTable .= '</table><br/><br/><br/>';
                $pdf->writeHTML($partDTable, true, 0, true, 0);

                $signBox1 = '<table cellspacing="0" cellpadding="4">';
                $signBox1 .= '<tr><td>' . $decoded[$language]['/SPI_RT/staffaudited:label'] . '</td></tr>';
                $signBox1 .= '<tr><td>' . $decoded[$language]['/SPI_RT/personincharge:label'] . $formData["personincharge"] . '</td></tr>';
                $signBox1 .= '</table>';
                $pdf->writeHTMLCell(90, 18, '', '', $signBox1, 1, 0, 0, true, 'L');

                $signBox2 = '<table cellspacing="0" cellpadding="4">';

                if ($language == 'Spanish') {
                    $signBox2 .= '<tr><td>Nombre y firma del auditor:</td></tr>';
                } else {
                    $signBox2 .= '<tr><td>' . $decoded[$language]['/SPI_RT/SUMMARY/info26:label'] . '</td></tr>';
                }

                if ($language == 'Portuguese') {
                    $signBox2 .= "<tr><td>Date " . $langDateFormat . ":</td></tr>";
                } else if ($language == 'Spanish') {
                    $signBox2 .= "<tr><td>Fecha " . $langDateFormat . ":</td></tr>";
                } else {
                    $signBox2 .= "<tr><td>Date " . $langDateFormat . ":</td></tr>";
                }
                $signBox2 .= '</table>';
                $pdf->writeHTMLCell(80, 18, 115, '', $signBox2, 1, 1, 0, true, 'L');
                //Close and output PDF document
                $fileName = "SPI-RT-CHECKLIST-" . date('d-M-Y-H-i-s') . ".pdf";
                if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download")) {
                    mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download");
                }
                if (!file_exists(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id)) {
                    mkdir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id);
                }
                $filePath = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id . DIRECTORY_SEPARATOR . $fileName;
                $pdf->Output($filePath, "F");
                //============================================================+
                // END OF FILE
                //============================================================+
            }
            //zip part
            $zip = new ZipArchive();
            $filename = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id . '.zip';
            if ($zip->open($filename, ZipArchive::CREATE) == true) {
                $file_list = scandir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id);
                if (count($file_list) > 2) {
                    foreach ($file_list as $file) {
                        if (in_array($file, array(".", ".."))) {
                            continue;
                        }

                        $zip->addFile(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id . DIRECTORY_SEPARATOR . $file, $file);
                    }
                }
            }
            $zip->close();
            //zip end
            //remove source pdf(s)
            $common->removeDirectory(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id);
        }
    }

    public function getV5DownloadDataList()
    {
        $common = new CommonService();
        $db = $this->sm->get('SpiFormVer5DownloadTable');
        $result = $db->fetchDownloadDataList();
        if (count($result['formResult']) > 0) {
            //get config details
            $globalDb = $this->sm->get('GlobalTable');
            $configData = $globalDb->getGlobalConfig();
            $configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "label-spi-v5.php";
            $fileContents = file_get_contents($configFile);
            //Convert the JSON string back into an array.
            $decoded = json_decode($fileContents, true);
            $language = $configData['language'];
            foreach ($result['formResult'] as $formData) {
                // create new PDF document
                $pdf = new TcpdfExtends(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->setSchemeName(ucwords($configData['header']), $configData['logo']);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('ODK DASHBOARD');
                $pdf->SetTitle('SPI-RT Checklist');
                $pdf->SetSubject('ODK DASHBOARD');
                $pdf->SetKeywords('odk', 'odk dashboard');

                // set default header data
                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

                // set header and footer fonts
                $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                // set auto page breaks
                $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // set some language-dependent strings (optional)
                if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
                    require_once dirname(__FILE__) . '/lang/eng.php';
                    $pdf->setLanguageArray($l);
                }
                // ---------------------------------------------------------

                // set font
                $pdf->SetFont('times', '', 10);

                // add a page
                $pdf->AddPage();
                //$pdf->SetY(20,true,false);
                $partA = '<p style="font-weight:bold;line-height:24px;">' . $decoded[$language]['/SPI_RT/TESTSITE/FACILITY/info2:label'] . '</p>';
                //$partA.='<br/>';

                $pdf->writeHTML($partA, true, 0, true, 0);

                $pdf->writeHTMLCell('', 12, '', '', '<p>' . $decoded[$language]['/SPI_RT/TESTSITE/FACILITY/info2:hint'] . '</p>', 0, 1, false, true, 'L', true);
                if ($language == 'Portuguese') {
                    $langDateFormat = '(dd/mm/aaaa)';
                } else if ($language == 'Spanish') {
                    $langDateFormat = '(dd/mm/aaaa)';
                } else {
                    $langDateFormat = '(dd/mm/yyyy)';
                }

                $fId = (isset($formData['facilityInfo']['ffId'])) ? ucwords($formData['facilityInfo']['ffId']) : '';
                $fName = (isset($formData['facilityInfo']['fName'])) ? ucwords($formData['facilityInfo']['fName']) : '';
                $testingTab = '<table border="1" cellspacing="0" cellpadding="5">';
                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/assesmentofaudit:label'] . '</b>' . $langDateFormat . ': ' . \Application\Service\CommonService::humanReadableDateFormat($formData['assesmentofaudit']) . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/auditroundno:label'] . '</b> ' . $formData['auditroundno'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/facilityname:label'] . '</b> ' . $fName . '</td>';
                if ($language == 'Portuguese') {
                    $testingTab .= '<td><b>Identificacao do local de testagem </b>(se aplicavel): ' . $fId . '</td>';
                } else if ($language == 'Spanish') {
                    $testingTab .= '<td><b>Tipo de sitio de pruebas </b>(seleccione uno): ' . $fId . '</td>';
                } else {
                    $testingTab .= '<td><b>Testing Facility ID</b>(if applicable) : ' . $fId . '</td>';
                }
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                //$testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/testingpointname:label'] . '</b> ' . $formData['testingpointname'] . '</td>';
                $testingTab .= '<td colspan="2"><b>' . $decoded[$language]['/SPI_RT/TESTSITE/testingpointtype:label'] . '</b> ' . $formData['testingpointtype'];
                $testingTab .= ((isset($formData['testingpointtype_other']) && $formData['testingpointtype_other'] != "") ? " - " . $formData['testingpointtype_other'] : "") . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td colspan="2"><b>' . $decoded[$language]['/SPI_RT/TESTSITE/locationaddress:label'] . '</b> ' . $formData['physicaladdress'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/level:label'] . '</b> <br/>' . $formData['level'];
                $testingTab .= ((isset($formData['level_other']) && $formData['level_other'] != "") ? " Other - " . $formData['level_other'] : "") . ':' . $formData['level_other'];
                $testingTab .= '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/affiliation:label'] . '</b><br/>' . $formData['affiliation'];
                $testingTab .= ((isset($formData['affiliation_other']) && $formData['affiliation_other'] != "") ? " Other : " . $formData['affiliation_other'] : "");
                $testingTab .= '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td colspan="2"><b>' . $decoded[$language]['/SPI_RT/TESTSITE/NumberofTester:label'] . '</b>' . $formData['NumberofTester'] . '</td>';
                //$testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/avgMonthTesting:label'] . '</b>' . $formData['avgMonthTesting'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/name_auditor_lead:label'] . '</b>' . $formData['name_auditor_lead'] . '</td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/name_auditor2:label'] . '</b>' . $formData['name_auditor2'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/clients_tested_hiv:label'] . '</b></td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_month:label'] . '</b>' . $formData['client_tested_HIV_PM'] . '<br/>';
                $testingTab .= '   <b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_quarter:label'] . '</b>' . $formData['client_tested_HIV_PQ'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/new_hiv:label'] . '</b></td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_month:label'] . '</b>' . $formData['client_newly_HIV_PM'] . '<br/>';
                $testingTab .= '   <b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_quarter:label'] . '</b>' . $formData['client_newly_HIV_PQ'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/hiv_negative:label'] . '</b></td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_month:label'] . '</b>' . $formData['client_negative_HIV_PM'] . '<br/>';
                $testingTab .= '   <b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_quarter:label'] . '</br>' . $formData['client_negative_HIV_PQ'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/newly_identified_positives:label'] . '</b></td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_month:label'] . '</b>' . $formData['client_positive_HIV_RTRI_PM'] . '<br/>';
                $testingTab .= '   <b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_quarter:label'] . '</br>' . $formData['client_positive_HIV_RTRI_PQ'] . '</td>';
                $testingTab .= '</tr>';

                $testingTab .= '<tr>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/recent_identified_positives:label'] . '</b></td>';
                $testingTab .= '<td><b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_month:label'] . '</b>' . $formData['client_recent_RTRI_PM'] . '<br/>';
                $testingTab .= '   <b>' . $decoded[$language]['/SPI_RT/TESTSITE/per_quarter:label'] . '</br>' . $formData['client_recent_RTRI_PQ'] . '</td>';
                $testingTab .= '</tr>';
                $testingTab .= '</table>';

                $pdf->writeHTML($testingTab, true, 0, true, 0);

                $partBHeading = '<b>' . $decoded[$language]['/SPI_RT/SPIRT/info4:label'] . '</b>';

                $pdf->writeHTML($partBHeading, true, 0, true, 0);

                $partBCont = '<br/><div>' . $decoded[$language]['/SPI_RT/SPIRT/info4:hint'] . '</div>';

                $pdf->writeHTML($partBCont, true, 0, true, 0);

                $partBTable = '<table border="1" cellspacing="0" cellpadding="5" style="width:100%;">';
                $partBTable .= "<tr>";
                $language;
                if ($language == 'Portuguese') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:52%;">SECÇÃO</td>';
                } else {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:52%;">SECTION</td>';
                }

                $partBTable .= '<td style="text-align:center;font-weight:bold;width:7%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_10/PERSONAL_Q_1_10/1:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_3/PERSONAL_Q_1_3/0.5:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:7%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_8/PERSONAL_Q_1_8/0:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;font-weight:bold;width:18%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_5/PERSONAL_C_1_5:label'] . '</td>';
                if ($language == 'Portuguese') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Pontuação</td>';
                } elseif ($language == 'Spanish') {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Punteo</td>';
                } else {
                    $partBTable .= '<td style="text-align:center;font-weight:bold;width:8%;">Score</td>';
                }

                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PERSONAL:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">10</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 11; $i++) {

                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_EQA_PT']) && $formData['PERSONAL_Q_1_' . $i . '_EQA_PT'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS']) && $formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT']) && $formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION']) && $formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS']) && $formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS'] == 1)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED']) && $formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED'] == 1)
                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_EQA_PT']) && $formData['PERSONAL_Q_1_' . $i . '_EQA_PT'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS']) && $formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT']) && $formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION']) && $formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS']) && $formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS'] == 0.5)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED']) && $formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TRAINING'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_TESTING_REGISTER'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_EQA_PT']) && $formData['PERSONAL_Q_1_' . $i . '_EQA_PT'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS']) && $formData['PERSONAL_Q_1_' . $i . '_QC_PROCESS'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT']) && $formData['PERSONAL_Q_1_' . $i . '_SAFETY_MANAGEMENT'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING']) && $formData['PERSONAL_Q_1_' . $i . '_REFRESHER_TRAINING'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING']) && $formData['PERSONAL_Q_1_' . $i . '_HIV_COMPETENCY_TESTING'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION']) && $formData['PERSONAL_Q_1_' . $i . '_NATIONAL_CERTIFICATION'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS']) && $formData['PERSONAL_Q_1_' . $i . '_CERTIFIED_TESTERS'] == 0)
                        || (isset($formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED']) && $formData['PERSONAL_Q_1_' . $i . '_RECERTIFIED'] == 0)
                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_HIV_TRAINING']) && $formData['PERSONAL_C_1_' . $i . '_HIV_TRAINING'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_HIV_TRAINING'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PERSONAL_C_1_' . $i . '_HIV_TESTING_REGISTER'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_HIV_TESTING_REGISTER'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_EQA_PT']) && $formData['PERSONAL_C_1_' . $i . '_EQA_PT'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_EQA_PT'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_QC_PROCESS']) && $formData['PERSONAL_C_1_' . $i . '_QC_PROCESS'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_QC_PROCESS'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_SAFETY_MANAGEMENT']) && $formData['PERSONAL_C_1_' . $i . '_SAFETY_MANAGEMENT'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_SAFETY_MANAGEMENT'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_REFRESHER_TRAINING']) && $formData['PERSONAL_C_1_' . $i . '_REFRESHER_TRAINING'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_REFRESHER_TRAINING'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_HIV_COMPETENCY_TESTING']) && $formData['PERSONAL_C_1_' . $i . '_HIV_COMPETENCY_TESTING'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_HIV_COMPETENCY_TESTING'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_NATIONAL_CERTIFICATION']) && $formData['PERSONAL_C_1_' . $i . '_NATIONAL_CERTIFICATION'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_NATIONAL_CERTIFICATION'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_CERTIFIED_TESTERS']) && $formData['PERSONAL_C_1_' . $i . '_CERTIFIED_TESTERS'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_CERTIFIED_TESTERS'];
                    }

                    if ((isset($formData['PERSONAL_C_1_' . $i . '_RECERTIFIED']) && $formData['PERSONAL_C_1_' . $i . '_RECERTIFIED'] !== '')) {
                        $comments = $formData['PERSONAL_C_1_' . $i . '_RECERTIFIED'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td style="width:52%;">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_' . $i . '/PERSONAL_Q_1_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PERSONAL/PERSONAL_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PERSONAL_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PHYSICAL:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">5</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 6; $i++) {
                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA']) && $formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA'] == 1)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_CLEAN_TESTING_AREA']) && $formData['PHYSICAL_Q_2_' . $i . '_CLEAN_TESTING_AREA'] == 1)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY'] == 1)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE'] == 1)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE'] == 1)
                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA']) && $formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA'] == 0.5)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PHYSICAL_Q_2_' . $i . '_HIV_TESTING_REGISTER'] == 0.5)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY'] == 0.5)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE'] == 0.5)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA']) && $formData['PHYSICAL_Q_2_' . $i . '_DESIGNATED_HIV_AREA'] == 0)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PHYSICAL_Q_2_' . $i . '_HIV_TESTING_REGISTER'] == 0)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY'] == 0)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_TEST_KIT_STORAGE'] == 0)
                        || (isset($formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE']) && $formData['PHYSICAL_Q_2_' . $i . '_SUFFICIENT_SECURE_STORAGE'] == 0)
                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['PHYSICAL_C_2_' . $i . '_DESIGNATED_HIV_AREA']) && $formData['PHYSICAL_C_2_' . $i . '_DESIGNATED_HIV_AREA'] !== '')) {
                        $comments = $formData['PHYSICAL_C_2_' . $i . '_DESIGNATED_HIV_AREA'];
                    }

                    if ((isset($formData['PHYSICAL_C_2_' . $i . '_HIV_TESTING_REGISTER']) && $formData['PHYSICAL_C_2_' . $i . '_HIV_TESTING_REGISTER'] !== '')) {
                        $comments = $formData['PHYSICAL_C_2_' . $i . '_HIV_TESTING_REGISTER'];
                    }

                    if ((isset($formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY']) && $formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY'] !== '')) {
                        $comments = $formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_LIGHT_AVAILABILITY'];
                    }

                    if ((isset($formData['PHYSICAL_C_2_' . $i . '_TEST_KIT_STORAGE']) && $formData['PHYSICAL_C_2_' . $i . '_TEST_KIT_STORAGE'] !== '')) {
                        $comments = $formData['PHYSICAL_C_2_' . $i . '_TEST_KIT_STORAGE'];
                    }

                    if ((isset($formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_SECURE_STORAGE']) && $formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_SECURE_STORAGE'] !== '')) {
                        $comments = $formData['PHYSICAL_C_2_' . $i . '_SUFFICIENT_SECURE_STORAGE'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/PHYSICAL/PHY_G_2_' . $i . '/PHYSICAL_Q_2_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PHYSICAL/PHYSICAL_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PHYSICAL_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/SAFETY:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">11</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 11; $i++) {

                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['SAFETY_Q_3_' . $i . '_IMPLEMENT_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . '_IMPLEMENT_SAFETY_PRACTICES'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE']) && $formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE']) && $formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE'] == 1)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED']) && $formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED'] == 1)
                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['SAFETY_Q_3_' . $i . '_IMPLEMENT_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . '_IMPLEMENT_SAFETY_PRACTICES'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE']) && $formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE']) && $formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE'] == 0.5)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED']) && $formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['SAFETY_Q_3_' . $i . 'IMPLEMENT_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . 'IMPLEMENT_SAFETY_PRACTICES'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE']) && $formData['SAFETY_Q_3_' . $i . '_ACCIDENTAL_EXPOSURE'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES']) && $formData['SAFETY_Q_3_' . $i . '_PRACTICE_SAFETY_PRACTICES'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_AVAILABILITY'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_PPE_USED_PROPERLY'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY']) && $formData['SAFETY_Q_3_' . $i . '_WATER_SOAP_AVAILABILITY'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_AVAILABLE'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY']) && $formData['SAFETY_Q_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE']) && $formData['SAFETY_Q_3_' . $i . '_SEGREGATION_OF_WASTE'] == 0)
                        || (isset($formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED']) && $formData['SAFETY_Q_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED'] == 0)
                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . 'IMPLEMENT_SAFETY_PRACTICES']) && $formData['SAFETY_C_3_' . $i . 'IMPLEMENT_SAFETY_PRACTICES'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . 'IMPLEMENT_SAFETY_PRACTICES'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_ACCIDENTAL_EXPOSURE']) && $formData['SAFETY_C_3_' . $i . '_ACCIDENTAL_EXPOSURE'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_ACCIDENTAL_EXPOSURE'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_PRACTICE_SAFETY_PRACTICES']) && $formData['SAFETY_C_3_' . $i . '_PRACTICE_SAFETY_PRACTICES'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_PRACTICE_SAFETY_PRACTICES'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_PPE_AVAILABILITY']) && $formData['SAFETY_C_3_' . $i . '_PPE_AVAILABILITY'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_PPE_AVAILABILITY'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_PPE_USED_PROPERLY']) && $formData['SAFETY_C_3_' . $i . '_PPE_USED_PROPERLY'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_PPE_USED_PROPERLY'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_WATER_SOAP_AVAILABILITY']) && $formData['SAFETY_C_3_' . $i . '_WATER_SOAP_AVAILABILITY'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_WATER_SOAP_AVAILABILITY'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_DISINFECTANT_AVAILABLE']) && $formData['SAFETY_C_3_' . $i . '_DISINFECTANT_AVAILABLE'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_DISINFECTANT_AVAILABLE'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY']) && $formData['SAFETY_C_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_DISINFECTANT_LABELED_PROPERLY'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_SEGREGATION_OF_WASTE']) && $formData['SAFETY_C_3_' . $i . '_SEGREGATION_OF_WASTE'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_SEGREGATION_OF_WASTE'];
                    }

                    if ((isset($formData['SAFETY_C_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED']) && $formData['SAFETY_C_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED'] !== '')) {
                        $comments = $formData['SAFETY_C_3_' . $i . '_INFECTIOUS_WASTE_EMPTIED'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/SAFETY/SAF_3_' . $i . '/SAFETY_Q_3_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/SAFETY/SAFETY_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['SAFETY_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/PRETEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">12</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 14; $i++) {
                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES']) && $formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM']) && $formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE']) && $formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION']) && $formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY']) && $formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT']) && $formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY']) && $formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION']) && $formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES']) && $formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION']) && $formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION'] == 1)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED']) && $formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED'] == 1)
                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES']) && $formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM']) && $formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE']) && $formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION']) && $formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY']) && $formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT']) && $formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY']) && $formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION']) && $formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES']) && $formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION']) && $formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION'] == 0.5)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED']) && $formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES']) && $formData['PRE_Q_4_' . $i . '_NATIONAL_GUIDELINES'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM']) && $formData['PRE_Q_4_' . $i . '_HIV_TESTING_ALGORITHM'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCESSIBLE'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE']) && $formData['PRE_Q_4_' . $i . '_TEST_PROCEDURES_ACCURATE'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE']) && $formData['PRE_Q_4_' . $i . '_APPROVED_KITS_AVAILABLE'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION']) && $formData['PRE_Q_4_' . $i . '_HIV_KITS_EXPIRATION'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY']) && $formData['PRE_Q_4_' . $i . '_KIT_SUPPLIES_AVAILABILITY'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT']) && $formData['PRE_Q_4_' . $i . '_STOCK_MANAGEMENT'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY']) && $formData['PRE_Q_4_' . $i . '_DOCUMENTED_INVENTORY'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION']) && $formData['PRE_Q_4_' . $i . '_SOPS_BLOOD_COLLECTION'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES']) && $formData['PRE_Q_4_' . $i . '_BLOOD_COLLECTION_SUPPLIES'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION']) && $formData['PRE_Q_4_' . $i . '_CLIENT_IDENTIFICATION'] == 0)
                        || (isset($formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED']) && $formData['PRE_Q_4_' . $i . '_CLIENT_ID_RECORDED'] == 0)
                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_NATIONAL_GUIDELINES']) && $formData['PRE_C_4' . $i . '_NATIONAL_GUIDELINES'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_NATIONAL_GUIDELINES'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_HIV_TESTING_ALGORITHM']) && $formData['PRE_C_4' . $i . '_HIV_TESTING_ALGORITHM'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_HIV_TESTING_ALGORITHM'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCESSIBLE']) && $formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCESSIBLE'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCESSIBLE'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCURATE']) && $formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCURATE'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_TEST_PROCEDURES_ACCURATE'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_APPROVED_KITS_AVAILABLE']) && $formData['PRE_C_4' . $i . '_APPROVED_KITS_AVAILABLE'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_APPROVED_KITS_AVAILABLE'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_HIV_KITS_EXPIRATION']) && $formData['PRE_C_4' . $i . '_HIV_KITS_EXPIRATION'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_HIV_KITS_EXPIRATION'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_KIT_SUPPLIES_AVAILABILITY']) && $formData['PRE_C_4' . $i . '_KIT_SUPPLIES_AVAILABILITY'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_KIT_SUPPLIES_AVAILABILITY'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_STOCK_MANAGEMENT']) && $formData['PRE_C_4' . $i . '_STOCK_MANAGEMENT'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_STOCK_MANAGEMENT'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_DOCUMENTED_INVENTORY']) && $formData['PRE_C_4' . $i . '_DOCUMENTED_INVENTORY'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_DOCUMENTED_INVENTORY'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_SOPS_BLOOD_COLLECTION']) && $formData['PRE_C_4' . $i . '_SOPS_BLOOD_COLLECTION'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_SOPS_BLOOD_COLLECTION'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_BLOOD_COLLECTION_SUPPLIES']) && $formData['PRE_C_4' . $i . '_BLOOD_COLLECTION_SUPPLIES'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_BLOOD_COLLECTION_SUPPLIES'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_CLIENT_IDENTIFICATION']) && $formData['PRE_C_4' . $i . '_CLIENT_IDENTIFICATION'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_CLIENT_IDENTIFICATION'];
                    }

                    if ((isset($formData['PRE_C_4' . $i . '_CLIENT_ID_RECORDED']) && $formData['PRE_C_4' . $i . '_CLIENT_ID_RECORDED'] !== '')) {
                        $comments = $formData['PRE_C_4' . $i . '_CLIENT_ID_RECORDED'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/PRETEST/PRE_4_' . $i . '/PRE_Q_4_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/PRETEST/PRETEST_Display:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['PRETEST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/TEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">9</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 10; $i++) {

                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM']) && $formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY']) && $formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY']) && $formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED']) && $formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL']) && $formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED']) && $formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS']) && $formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 1)
                        || (isset($formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS']) && $formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS'] == 1)

                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM']) && $formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY']) && $formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY']) && $formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED']) && $formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL']) && $formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED']) && $formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS']) && $formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 0.5)
                        || (isset($formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS']) && $formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM']) && $formData['TEST_Q_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY']) && $formData['TEST_Q_5_' . $i . '_TIMERS_AVAILABILITY'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY']) && $formData['TEST_Q_5_' . $i . '_SAMPLE_DEVICE_ACCURACY'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED']) && $formData['TEST_Q_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL']) && $formData['TEST_Q_5_' . $i . '_QUALITY_CONTROL'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED']) && $formData['TEST_Q_5_' . $i . '_QC_RESULTS_RECORDED'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS']) && $formData['TEST_Q_5_' . $i . '_INCORRECT_QC_RESULTS'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['TEST_Q_5_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 0)
                        || (isset($formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS']) && $formData['TEST_Q_5_' . $i . '_REVIEW_QC_RECORDS'] == 0)

                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM']) && $formData['TEST_C_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_PROCEDURES_TESTING_ALGORITHM'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_TIMERS_AVAILABILITY']) && $formData['TEST_C_5_' . $i . '_TIMERS_AVAILABILITY'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_TIMERS_AVAILABILITY'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_SAMPLE_DEVICE_ACCURACY']) && $formData['TEST_C_5_' . $i . '_SAMPLE_DEVICE_ACCURACY'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_SAMPLE_DEVICE_ACCURACY'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED']) && $formData['TEST_C_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_TESTING_PROCEDURE_FOLLOWED'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_QUALITY_CONTROL']) && $formData['TEST_C_5_' . $i . '_QUALITY_CONTROL'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_QUALITY_CONTROL'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_QC_RESULTS_RECORDED']) && $formData['TEST_C_5_' . $i . '_QC_RESULTS_RECORDED'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_QC_RESULTS_RECORDED'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_INCORRECT_QC_RESULTS']) && $formData['TEST_C_5_' . $i . '_INCORRECT_QC_RESULTS'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_INCORRECT_QC_RESULTS'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_REVIEW_QC_RECORDS']) && $formData['TEST_C_5_' . $i . '_REVIEW_QC_RECORDS'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_REVIEW_QC_RECORDS'];
                    }

                    if ((isset($formData['TEST_C_5_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['TEST_C_5_' . $i . '_APPROPRIATE_STEPS_TAKEN'] !== '')) {
                        $comments = $formData['TEST_C_5_' . $i . '_APPROPRIATE_STEPS_TAKEN'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/TEST/TEST_5_' . $i . '/TEST_Q_5_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/TEST/TEST_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['TEST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/POSTTEST:label'] . '</td>';
                //$partBTable.='<td style="text-align:center;font-weight:bold;background-color:#dddbdb;">9</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 10; $i++) {

                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER']) && $formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY']) && $formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY']) && $formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED']) && $formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT']) && $formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION']) && $formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION'] == 1)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED'] == 1)

                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER']) && $formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY']) && $formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY']) && $formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED']) && $formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT']) && $formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION']) && $formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION'] == 0.5)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER']) && $formData['POST_Q_6_' . $i . '_STANDARDIZED_HIV_REGISTER'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY']) && $formData['POST_Q_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY']) && $formData['POST_Q_6_' . $i . '_PAGE_TOTAL_SUMMARY'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED']) && $formData['POST_Q_6_' . $i . '_INVALID_TEST_RESULT_RECORDED'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['POST_Q_6_' . $i . '_APPROPRIATE_STEPS_TAKEN'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_REVIEWED'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT']) && $formData['POST_Q_6_' . $i . '_DOCUMENTS_SECURELY_KEPT'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION']) && $formData['POST_Q_6_' . $i . '_REGISTER_SECURE_LOCATION'] == 0)
                        || (isset($formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED']) && $formData['POST_Q_6_' . $i . '_REGISTERS_PROPERLY_LABELED'] == 0)

                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_STANDARDIZED_HIV_REGISTER']) && $formData['POST_C_6_' . $i . '_STANDARDIZED_HIV_REGISTER'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_STANDARDIZED_HIV_REGISTER'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY']) && $formData['POST_C_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_ELEMENTS_CAPTURED_CORRECTLY'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_PAGE_TOTAL_SUMMARY']) && $formData['POST_C_6_' . $i . '_PAGE_TOTAL_SUMMARY'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_PAGE_TOTAL_SUMMARY'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_INVALID_TEST_RESULT_RECORDED']) && $formData['POST_C_6_' . $i . '_INVALID_TEST_RESULT_RECORDED'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_INVALID_TEST_RESULT_RECORDED'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_APPROPRIATE_STEPS_TAKEN']) && $formData['POST_C_6_' . $i . '_APPROPRIATE_STEPS_TAKEN'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_APPROPRIATE_STEPS_TAKEN'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_REGISTERS_REVIEWED']) && $formData['POST_C_6_' . $i . '_REGISTERS_REVIEWED'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_REGISTERS_REVIEWED'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_DOCUMENTS_SECURELY_KEPT']) && $formData['POST_C_6_' . $i . '_DOCUMENTS_SECURELY_KEPT'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_DOCUMENTS_SECURELY_KEPT'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_REGISTER_SECURE_LOCATION']) && $formData['POST_C_6_' . $i . '_REGISTER_SECURE_LOCATION'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_REGISTER_SECURE_LOCATION'];
                    }

                    if ((isset($formData['POST_C_6_' . $i . '_REGISTERS_PROPERLY_LABELED']) && $formData['POST_C_6_' . $i . '_REGISTERS_PROPERLY_LABELED'] !== '')) {
                        $comments = $formData['POST_C_6_' . $i . '_REGISTERS_PROPERLY_LABELED'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/POSTTEST/POST_6_' . $i . '/POST_Q_6_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/POSTTEST/POST_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['POST_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/EQA:label'] . '</td>';
                $partBTable .= '</tr>';

                for ($i = 1; $i < 9; $i++) {

                    $yes = "";
                    $no = "";
                    $partial = "";
                    $comments = "";

                    if ((isset($formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT']) && $formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES']) && $formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION']) && $formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION']) && $formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS']) && $formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED'] == 1)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS']) && $formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS'] == 1)

                    ) {
                        $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT']) && $formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES']) && $formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION']) && $formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION']) && $formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS']) && $formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED'] == 0.5)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS']) && $formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS'] == 0.5)
                    ) {
                        $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    } elseif ((isset($formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT']) && $formData['EQA_Q_7_' . $i . '_PT_ENROLLMENT'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES']) && $formData['EQA_Q_7_' . $i . '_TESTING_EQAPT_SAMPLES'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION']) && $formData['EQA_Q_7_' . $i . '_REVIEW_BEFORE_SUBMISSION'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION']) && $formData['EQA_Q_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS']) && $formData['EQA_Q_7_' . $i . '_RECEIVE_PERIODIC_VISITS'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED']) && $formData['EQA_Q_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED'] == 0)
                        || (isset($formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS']) && $formData['EQA_Q_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS'] == 0)
                    ) {
                        $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_PT_ENROLLMENT']) && $formData['EQA_C_7_' . $i . '_PT_ENROLLMENT'] !== '')) {
                        $comments = $formData['EQA_C_6_' . $i . '_PT_ENROLLMENT'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_TESTING_EQAPT_SAMPLES']) && $formData['EQA_C_7_' . $i . '_TESTING_EQAPT_SAMPLES'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_TESTING_EQAPT_SAMPLES'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_REVIEW_BEFORE_SUBMISSION']) && $formData['EQA_C_7_' . $i . '_REVIEW_BEFORE_SUBMISSION'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_REVIEW_BEFORE_SUBMISSION'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED']) && $formData['EQA_C_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_FEEDBACK_RECEIVED_REVIEWED'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION']) && $formData['EQA_C_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_IMPLEMENT_CORRECTIVE_ACTION'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_RECEIVE_PERIODIC_VISITS']) && $formData['EQA_C_7_' . $i . '_RECEIVE_PERIODIC_VISITS'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_RECEIVE_PERIODIC_VISITS'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED']) && $formData['EQA_C_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_FEEDBACK_PROVIDED_DOCUMENTED'];
                    }

                    if ((isset($formData['EQA_C_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS']) && $formData['EQA_C_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS'] !== '')) {
                        $comments = $formData['EQA_C_7_' . $i . '_TESTERS_RETRAINED_IN_VISITS'];
                    }

                    $partBTable .= '<tr>';

                    $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/EQA/EQA_7_' . $i . '/EQA_Q_7_' . $i . ':label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $yes;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $partial;
                    $partBTable .= '</td>';

                    $partBTable .= '<td style="text-align:center;">';
                    $partBTable .= $no;
                    $partBTable .= '</td>';

                    $partBTable .= '<td>' . ($comments) . '</td>';
                    if (trim($yes) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                    }

                    if (trim($partial) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                    }

                    if (trim($no) != "") {
                        $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                    }

                    $partBTable .= '</tr>';
                }
                $partBTable .= '<tr>';
                $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/EQA/EQA_DISPLAY:label'] . '</td>';
                $partBTable .= '<td style="text-align:center;">' . $formData['EQA_SCORE'] . '</td>';
                $partBTable .= '</tr>';

                $partBTable .= '<tr>';
                if (strtolower($formData['performrtritesting']) == 'yes') {
                    $partBTable .= '<td colspan="6" style="text-align:center;font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/EQA/sampleretesting:label'] . ' - Yes </td>';
                } else {
                    $partBTable .= '<td colspan="6" style="text-align:center;font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/EQA/sampleretesting:label'] . '- No</td>';
                }

                $partBTable .= '</tr>';

                if (strtolower($formData['performrtritesting']) == 'yes') {
                    $partBTable .= '<tr>';
                    $partBTable .= '<td colspan="6" style="font-weight:bold;background-color:#dddbdb;">' . $decoded[$language]['/SPI_RT/INFECTIONSUR:label'] . '</td>';
                    $partBTable .= '</tr>';

                    for ($i = 1; $i < 12; $i++) {

                        $yes = "";
                        $no = "";
                        $partial = "";
                        $comments = "";

                        if ((isset($formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE']) && $formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED']) && $formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED']) && $formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED'] == 1)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS']) && $formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS'] == 1)

                        ) {
                            $yes = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                        } elseif ((isset($formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE']) && $formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED']) && $formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED']) && $formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED'] == 0.5)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS']) && $formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS'] == 0.5)
                        ) {
                            $partial = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                        } elseif ((isset($formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY']) && $formData['RTRI_Q_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_JOBAIDS_READILY_AVAILABLE'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE']) && $formData['RTRI_Q_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE']) && $formData['RTRI_Q_8_' . $i . '_RTRI_KIT_STORAGE'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED']) && $formData['RTRI_Q_8_' . $i . '_QC_ROUTINELY_USED'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED']) && $formData['RTRI_Q_8_' . $i . '_QC_RESULTS_RECORDED'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED']) && $formData['RTRI_Q_8_' . $i . '_INCORRECT_QC_DOCUMENTED'] == 0)
                            || (isset($formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS']) && $formData['RTRI_Q_8_' . $i . '_INVALID_RTRI_RESULTS'] == 0)
                        ) {
                            $no = '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">';
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING']) && $formData['RTRI_C_8_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING'] !== '')) {
                            $comments = $formData['EQA_C_6_' . $i . '_TESTERS_RECEIVED_RTRI_TRAINING'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY']) && $formData['RTRI_C_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_TESTERS_DEMONSTRATED_COMPETENCY'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_JOBAIDS_READILY_AVAILABLE']) && $formData['RTRI_C_8_' . $i . '_JOBAIDS_READILY_AVAILABLE'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_JOBAIDS_READILY_AVAILABLE'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE']) && $formData['RTRI_C_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_SUFFICIENT_SUPPLY_AVAILABLE'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_RTRI_KIT_STORAGE']) && $formData['RTRI_C_8_' . $i . '_RTRI_KIT_STORAGE'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_RTRI_KIT_STORAGE'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED']) && $formData['RTRI_C_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_RTRI_TESTING_PROCEDURE_FOLLOWED'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED']) && $formData['RTRI_C_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_RTRI_TESTING_RESULTS_DOCUMENTED'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_QC_ROUTINELY_USED']) && $formData['RTRI_C_8_' . $i . '_QC_ROUTINELY_USED'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_QC_ROUTINELY_USED'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_QC_RESULTS_RECORDED']) && $formData['RTRI_C_8_' . $i . '_QC_RESULTS_RECORDED'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_QC_RESULTS_RECORDED'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_INCORRECT_QC_DOCUMENTED']) && $formData['RTRI_C_8_' . $i . '_INCORRECT_QC_DOCUMENTED'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_INCORRECT_QC_DOCUMENTED'];
                        }

                        if ((isset($formData['RTRI_C_8_' . $i . '_INVALID_RTRI_RESULTS']) && $formData['RTRI_C_8_' . $i . '_INVALID_RTRI_RESULTS'] !== '')) {
                            $comments = $formData['RTRI_C_8_' . $i . '_INVALID_RTRI_RESULTS'];
                        }

                        $partBTable .= '<tr>';

                        $partBTable .= '<td>' . $decoded[$language]['/SPI_RT/INFECTIONSUR/RTRI_8_' . $i . '/RTRI_Q_8_' . $i . ':label'] . '</td>';
                        $partBTable .= '<td style="text-align:center;">';
                        $partBTable .= $yes;
                        $partBTable .= '</td>';

                        $partBTable .= '<td style="text-align:center;">';
                        $partBTable .= $partial;
                        $partBTable .= '</td>';

                        $partBTable .= '<td style="text-align:center;">';
                        $partBTable .= $no;
                        $partBTable .= '</td>';

                        $partBTable .= '<td>' . ($comments) . '</td>';
                        if (trim($yes) != "") {
                            $partBTable .= '<td style="text-align:center;">' . 1 . '</td>';
                        }

                        if (trim($partial) != "") {
                            $partBTable .= '<td style="text-align:center;">' . 0.5 . '</td>';
                        }

                        if (trim($no) != "") {
                            $partBTable .= '<td style="text-align:center;">' . 0 . '</td>';
                        }

                        $partBTable .= '</tr>';
                    }
                    $partBTable .= '<tr>';
                    $partBTable .= '<td colspan="5" style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/INFECTIONSUR/RTRI_DISPLAY:label'] . '</td>';
                    $partBTable .= '<td style="text-align:center;">' . $formData['RTRI_SCORE'] . '</td>';
                    $partBTable .= '</tr>';
                }

                $partBTable .= '</table>';
                if ($language == 'Portuguese') {
                    $partBTable .= '<p>*A area marcada com asteriscos so é aplicavel para os locais onde as amostras retestadas sao executadas.</p>';
                } else if ($language == 'Spanish') {
                    $partBTable .= '<p>*Lo que aparece marcado con un asterisco son solo aplicables a sitios donde la repetición de las pruebas se hace.</p>';
                } else {
                    $partBTable .= '<p>*Those marked with an asterisk are only applicable to sites where sample retesting is performed.</p>';
                }

                $pdf->writeHTML($partBTable, true, 0, true, 0);

                $partC = '<br/><p style="font-weight:bold;">' . $decoded[$language]['/SPI_RT/scoring/info5:label'] . '</p>';
                $partC .= '<br/><span>' . $decoded[$language]['/SPI_RT/scoring/info6:label'] . '</span>';
                $partC .= '<p>' . $decoded[$language]['/SPI_RT/scoring/info10:label'] . '</p>';
                $partC .= '<p>' . $decoded[$language]['/SPI_RT/scoring/info11:label'] . '</p>';

                $pdf->writeHTML($partC, true, 0, true, 0);

                $summaryExp = explode(PHP_EOL, $decoded[$language]['/SPI_RT/SUMMARY/info17:label']);
                $totPointScored = '';
                $totExpectScored = '';
                $perScored = '';
                if (isset($summaryExp[8]) && trim($summaryExp[8]) != "") {
                    $totPointScored = $summaryExp[8];
                }
                if (isset($summaryExp[9]) && trim($summaryExp[9]) != "") {
                    $totExpectScored = $summaryExp[9];
                }
                if (isset($summaryExp[10]) && trim($summaryExp[10]) != "") {
                    $expPerScored = explode("=", $summaryExp[10]);
                    $perScored = (string) $expPerScored[0];
                }

                $partCTable = '<table border="1" cellspacing="0" cellpadding="5">';

                $partCTable .= '<tr style="font-weight:bold;text-align:center;">';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td style="width:15%">NIVEL</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td style="width:15%">Nivel</td>';
                } else {
                    $partCTable .= '<td style="width:15%">Levels</td>';
                }

                if ($language == 'Portuguese') {
                    $partCTable .= '<td  style="width:25%">PONTUACAO EM %</td>';
                    $partCTable .= '<td  style="width:60%">DESCRIÇAO DOS RESULTADOS</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td  style="width:25%">% Puntaje</td>';
                    $partCTable .= '<td  style="width:60%">Descripción de los resultados</td>';
                } else {
                    $partCTable .= '<td  style="width:25%">' . $perScored . '</td>';
                    $partCTable .= '<td  style="width:60%">Description of results</td>';
                }
                $partCTable .= '</tr>';

                $level0 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info21:label']);
                $level1 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info22:label']);
                if (count($level1) > 2) {
                    $level1[1] = $level1[1] . " - " . $level1[2];
                }
                $level2 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info23:label']);
                if (count($level2) > 2) {
                    $level2[1] = $level2[1] . " - " . $level2[2];
                }
                $level3 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info24:label']);
                if (count($level3) > 2) {
                    $level3[1] = $level3[1] . " - " . $level3[2];
                }
                $level4 = explode("-", $decoded[$language]['/SPI_RT/SUMMARY/info25:label']);

                if ($language == 'Spanish') {
                    $level0[0] = "Nivel 0";
                    $level0[1] = "Menos de 40% ";
                    $level1[0] = "Nivel 1";
                    $level2[0] = "Nivel 2";
                    $level3[0] = "Nivel 3";
                    $level4[0] = "Nivel 4";
                    $level4[1] = "90% a más";
                }

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#C00000;">' . $level0[0] . '</td>';
                $partCTable .= '<td>' . $level0[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Necessidade de melhoria em todas as areas e remediaçoes imediatas</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Necesita mejorar en todas las áreas y es necesaria corrección inmediata</td>';
                } else {
                    $partCTable .= '<td>Needs improvement in all areas and immediate remediation</td>';
                }

                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#E36C0A;">' . $level1[0] . '</td>';
                $partCTable .= '<td>' . $level1[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Necessidade de melhorias em areas especificas</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Necesita mejorar en áreas específicas</td>';
                } else {
                    $partCTable .= '<td>Needs improvement in specific areas</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#FFFF00;">' . $level2[0] . '</td>';
                $partCTable .= '<td>' . $level2[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Parcialmente admissivel ou aceitavel</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Parcialmente elegible</td>';
                } else {
                    $partCTable .= '<td>Partially eligible</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#92D050;">' . $level3[0] . '</td>';
                $partCTable .= '<td>' . $level3[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Proximo da certificaçao nacional</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Cercano a sitio nacional certificado</td>';
                } else {
                    $partCTable .= '<td>Close to national site certification</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '<tr>';
                $partCTable .= '<td style="background-color:#00B050;">' . $level4[0] . '</td>';
                $partCTable .= '<td>' . $level4[1] . '</td>';
                if ($language == 'Portuguese') {
                    $partCTable .= '<td>Admissivel a certificaçao nacional</td>';
                } else if ($language == 'Spanish') {
                    $partCTable .= '<td>Elegible para ser certificado</td>';
                } else {
                    $partCTable .= '<td>Eligible to national site certification</td>';
                }
                $partCTable .= '</tr>';

                $partCTable .= '</table>';

                $pdf->writeHTML($partCTable, true, 0, true, 0);
                $summationExp = explode(PHP_EOL, $decoded[$language]['/SPI_RT/SUMMARY/info12:label']);
                $facilityName = '';
                $heading = '';
                if (isset($summationExp[0]) && trim($summationExp[0]) != "") {
                    $heading = $summationExp[0];
                }
                if (isset($summationExp[2]) && trim($summationExp[2]) != "") {
                    $facilityName = $summationExp[2];
                }
                if (isset($summationExp[3]) && trim($summationExp[3]) != "") {
                    $auditorName = $summationExp[3];
                }
                if (isset($summationExp[4]) && trim($summationExp[4]) != "") {
                    $textPointName = $summationExp[4];
                }
                $staffAuditedName = '';
                $noOfTester = '';
                if (isset($summationExp[5]) && trim($summationExp[5]) != "") {
                    $expStaffAuditedName = explode(":", $summationExp[5]);
                    $staffAuditedName = $expStaffAuditedName[0];
                    $noOfTester = $expStaffAuditedName[1];
                }
                if ($language == 'Spanish') {
                    $heading = "PARTE D: Informe resumido del evaluador de la auditoría SPI-RT";
                }

                $partDTitle = '<p style="font-weight:bold;line-height:30px;">' . $heading . '</p>';
                $pdf->writeHTML($partDTitle, true, 0, true, 0);

                $partDtableBox1 = '<table cellspacing="0" cellpadding="2">';
                $partDtableBox1 .= "<tr><td>" . $facilityName . $fName . "</td></tr>";

                $partDtableBox1 .= "<tr><td>";
                if ($language == 'Portuguese') {
                    $partDtableBox1 .= "Tipo de local:";
                } else if ($language == 'Spanish') {
                    $partDtableBox1 .= "Tipo de sitio:";
                } else {
                    $partDtableBox1 .= "Site Type:";
                }
                $partDtableBox1 .= $formData['testingpointtype'];

                $partDtableBox1 .= ((isset($formData['testingpointtype_other']) && $formData['testingpointtype_other'] != "") ? " - " . $formData['testingpointtype_other'] : "");
                $partDtableBox1 .= "</td></tr>";
                $partDtableBox1 .= "<tr><td>" . $staffAuditedName . ":" . $formData['staffaudited'] . "</td></tr>";
                $partDtableBox1 .= '</table>';
                $pdf->writeHTMLCell(50, 26, '', '', $partDtableBox1, 1, 0, 0, true, 'L');

                $partDtableBox2 = '<table cellspacing="0" cellpadding="5">';
                $partDtableBox2 .= "<tr><td>" . $noOfTester . ": " . $formData['NumberofTester'] . "</td></tr><tr><td>" . $decoded[$language]['/SPI_RT/durationaudit:label'] . $formData['durationaudit'] . "</td></tr>";
                $partDtableBox2 .= '</table>';

                $pdf->writeHTMLCell(50, 26, 70, '', $partDtableBox2, 1, 0, 0, true, 'L', true);

                $scorePer = round($formData['AUDIT_SCORE_PERCENTAGE']);
                $level = '';
                $colorCode = '';
                if ($scorePer < 40) {
                    $level = $level0[0];
                    //$level="Level 0";
                    $colorCode = "background-color:#C00000";
                } elseif ($scorePer >= 40 && $scorePer <= 59) {
                    //$level="Level 1";
                    $level = $level1[0];
                    $colorCode = "background-color:#E36C0A";
                } elseif ($scorePer >= 60 && $scorePer <= 79) {
                    //$level="Level 2";
                    $level = $level2[0];
                    $colorCode = "background-color:#FFFF00";
                } elseif ($scorePer >= 80 && $scorePer <= 89) {
                    //$level="Level 3";
                    $level = $level3[0];
                    $colorCode = "background-color:#92D050";
                } elseif ($scorePer >= 90) {
                    //$level="Level 4";
                    $level = $level4[0];
                    $colorCode = "background-color:#00B050";
                }

                $partDtableBox3 = '<table cellspacing="0" cellpadding="5">';
                $partDtableBox3 .= "<tr><td>" . $totPointScored . $formData['FINAL_AUDIT_SCORE'] . "</td></tr>";
                $partDtableBox3 .= "<tr><td>" . $totExpectScored . $formData['MAX_AUDIT_SCORE'] . "</td></tr>";
                $partDtableBox3 .= '<tr><td>' . $perScored . "= " . round($formData['AUDIT_SCORE_PERCENTAGE'], 2) . '% &nbsp; <span style="' . $colorCode . '">  &nbsp;&nbsp;' . $level . '  &nbsp;&nbsp;</span></td></tr>';
                $partDtableBox3 .= '</table>';

                $pdf->writeHTMLCell(70, 26, 125, '', $partDtableBox3, 1, 1, 0, true, 'L', true);

                // set recommend
                $recommend = explode("-", $decoded[$language]['/SPI_RT/correctiveaction/action:label']);
                $timeLine = explode("-", $decoded[$language]['/SPI_RT/correctiveaction/timeline:label']);

                $partDTable = '<br/><br/><table border="1" cellspacing="0" cellpadding="5" style="width:100%">';
                $partDTable .= '<tr>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:9%"><br/><br/>' . $decoded[$language]['/SPI_RT/correctiveaction/sectionno:label'] . '</td>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:22%"><br/><br/>' . $decoded[$language]['/SPI_RT/correctiveaction/deficiency:label'] . '</td>';
                $partDTable .= '<td colspan="2" style="font-weight:bold;text-align:center;width:20%">' . $decoded[$language]['/SPI_RT/correctiveaction/correction:label'] . '</td>';
                $partDTable .= '<td rowspan="2" style="font-weight:bold;text-align:center;width:23%">' . $decoded[$language]['/SPI_RT/correctiveaction/auditorcomment:label'] . '</td>';
                $partDTable .= '<td colspan="2" style="font-weight:bold;text-align:center;width:26%">' . $recommend[0] . '</td>';
                $partDTable .= '</tr>';
                $partDTable .= '<tr>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;width:12%;">' . $decoded[$language]['/SPI_RT/correctiveaction/correction/Immediate:label'] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;width:8%;">' . $decoded[$language]['/SPI_RT/correctiveaction/correction/Followup:label'] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;">' . $recommend[1] . '</td>';
                $partDTable .= '<td style="font-weight:bold;text-align:center;">' . $timeLine[1] . '</td>';
                $partDTable .= '</tr>';
                if (isset($formData['correctiveaction']) && $formData['correctiveaction'] != "" && $formData['correctiveaction'] != "[]") {
                    $correctiveActions = json_decode($formData['correctiveaction'], true);
                    foreach ($correctiveActions as $ca) {
                        $partDTable .= '<tr>';
                        $partDTable .= '<td style="text-align:center;">' . $ca['sectionno'] . '</td>';
                        $partDTable .= '<td>' . $ca['deficiency'] . '</td>';
                        $partDTable .= '<td style="text-align:center;">';
                        $partDTable .= ($ca['correction'] == 'Immediate' ? '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">' : "");
                        $partDTable .= '</td>';
                        $partDTable .= '<td style="text-align:center;">';
                        $partDTable .= ($ca['correction'] == 'Followup' ? '<img src="' . APPLICATION_PATH . '/img/black-tick.png' . '" width="20">' : "");
                        $partDTable .= '</td>';
                        $partDTable .= '<td>' . $ca['auditorcomment'] . '</td>';
                        $partDTable .= '<td>' . $ca['action'] . '</td>';
                        $partDTable .= '<td>' . $ca['timeline'] . '</td>';
                        $partDTable .= '</tr>';
                    }
                } else {
                    $partDTable .= '<tr>';
                    $partDTable .= '<td colspan="7">' . $decoded[$language]['/SPI_RT/PERSONAL/PER_G_1_8/PERSONAL_Q_1_8/0:label'] . ' ' . $decoded[$language]['SPI_RT/correctiveaction:label'] . '</td>';
                    $partDTable .= '</tr>';
                }
                $partDTable .= '</table><br/><br/><br/>';
                $pdf->writeHTML($partDTable, true, 0, true, 0);

                $signBox1 = '<table cellspacing="0" cellpadding="4">';
                $signBox1 .= '<tr><td>' . $decoded[$language]['/SPI_RT/staffaudited:label'] . '</td></tr>';
                $signBox1 .= '<tr><td>' . $decoded[$language]['/SPI_RT/personincharge:label'] . $formData["personincharge"] . '</td></tr>';
                $signBox1 .= '</table>';
                $pdf->writeHTMLCell(90, 18, '', '', $signBox1, 1, 0, 0, true, 'L');

                $signBox2 = '<table cellspacing="0" cellpadding="4">';

                if ($language == 'Spanish') {
                    $signBox2 .= '<tr><td>Nombre y firma del auditor:</td></tr>';
                } else {
                    $signBox2 .= '<tr><td>' . $decoded[$language]['/SPI_RT/SUMMARY/info26:label'] . '</td></tr>';
                }

                if ($language == 'Portuguese') {
                    $signBox2 .= "<tr><td>Date " . $langDateFormat . ":</td></tr>";
                } else if ($language == 'Spanish') {
                    $signBox2 .= "<tr><td>Fecha " . $langDateFormat . ":</td></tr>";
                } else {
                    $signBox2 .= "<tr><td>Date " . $langDateFormat . ":</td></tr>";
                }
                $signBox2 .= '</table>';
                $pdf->writeHTMLCell(80, 18, 115, '', $signBox2, 1, 1, 0, true, 'L');

                //Close and output PDF document
                $fileName = "SPI-RT-CHECKLIST-" . date('d-M-Y-H-i-s') . ".pdf";
                if (isset($tempId)) {
                    $filePath = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "audit-email" . DIRECTORY_SEPARATOR . $tempId . DIRECTORY_SEPARATOR . $fileName;
                    $pdf->Output($filePath, "F");
                } else {
                    $filePath = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName;
                    //$pdf->Output('example_003.pdf', 'I');
                    $pdf->Output($filePath, "I");
                }
                //echo $fileName;
                //============================================================+
                // END OF FILE
                //============================================================+
            }
            //zip part
            $zip = new ZipArchive();
            $filename = TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id . '.zip';
            if ($zip->open($filename, ZipArchive::CREATE) == true) {
                $file_list = scandir(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id);
                if (count($file_list) > 2) {
                    foreach ($file_list as $file) {
                        if (in_array($file, array(".", ".."))) {
                            continue;
                        }

                        $zip->addFile(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id . DIRECTORY_SEPARATOR . $file, $file);
                    }
                }
            }
            $zip->close();
            //zip end
            //remove source pdf(s)
            $common->removeDirectory(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "download" . DIRECTORY_SEPARATOR . $result['downloadResult']->r_download_id);
        }
    }

    public function removeAudit($params)
    {
        $db = $this->sm->get('SpiFormVer3DuplicateTable');
        return $db->removeAuditData($params);
    }

    public function getV5DownloadFilesRow()
    {
        $db = $this->sm->get('SpiFormVer5DownloadTable');
        $result = $db->fetchDownloadFilesRow();
        $xlsx = new SimpleXLSXGen();
        $output = array();
        $headerRow = ['Audit Round No', 'Audit Date', 'Testing Point Type','Level', 'Affiliation', 'Audit Score'];
        $output[] = $headerRow;
        if(count($result) > 0){
            foreach($result as $data){
                $row = array();
                $row[] = trim($data['auditroundno']) == '' ? 'All' : $data['auditroundno'];;
                $row[] = $data['assesmentofaudit'];
                $row[] = trim($data['testingpointtype']) == '' ? 'All' : $data['testingpointtype'];
                $row[] = trim($data['level']) == '' ? 'All' : $data['level'];
                $row[] = trim($data['affiliation']) == '' ? 'All' : $data['affiliation'];
                $row[] = trim($data['AUDIT_SCORE_PERCENTAGE']) == '' ? 'All' : $data['AUDIT_SCORE_PERCENTAGE'];
                $output[] = $row;
            }
        }
        $xlsx->addSheet($output);
        $filename = 'Spi-v5-'.time().'.xlsx';
        return $xlsx->downloadAs($filename);
    }
    public function getDownloadFilesRow()
    {
        $db = $this->sm->get('SpiFormVer3DownloadTable');
        $result = $db->fetchDownloadFilesRow();
        $xlsx = new SimpleXLSXGen();
        $output = array();
        $headerRow = ['Audit Round No', 'Audit Date', 'Testing Point Type', 'Testing Point Name', 'Level', 'Affiliation', 'Level Name', 'Audit Score'];
        $output[] = $headerRow;
        if(count($result) > 0){
            foreach($result as $data){
                $row = array();
                $row[] = trim($data['auditroundno']) == '' ? 'All' : $data['auditroundno'];;
                $row[] = $data['assesmentofaudit'];
                $row[] = trim($data['testingpointtype']) == '' ? 'All' : $data['testingpointtype'];
                $row[] = trim($data['testingpointname']) == '' ? 'All' : $data['testingpointname'];
                $row[] = trim($data['level']) == '' ? 'All' : $data['level'];
                $row[] = trim($data['affiliation']) == '' ? 'All' : $data['affiliation'];
                $row[] = trim($data['level_name']) == '' ? 'All' : $data['level_name'];
                $row[] = trim($data['AUDIT_SCORE_PERCANTAGE']) == '' ? 'All' : $data['AUDIT_SCORE_PERCANTAGE'];
                $output[] = $row;
            }
        }
        $xlsx->addSheet($output);
        $filename = 'Spi-v3-'.time().'.xlsx';
        return $xlsx->downloadAs($filename);
    }

    public function validateSPIV3File($params)
    {
        $db = $this->sm->get('SpiFormVer3TempTable');
        $dbMain = $this->sm->get('SpiFormVer3Table');
        $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
        $sql = new Sql($this->adapter);
        $fileName = preg_replace('/[^A-Za-z0-9.]/', '-', $_FILES['fileName']['name']);
        $fileName = str_replace(" ", "-", $fileName);
        $ranNumber = str_pad(rand(0, pow(10, 6) - 1), 6, '0', STR_PAD_LEFT);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileName = $ranNumber . "." . $extension;
        if (!file_exists(UPLOAD_PATH) && !is_dir(UPLOAD_PATH)) {
            mkdir(APPLICATION_PATH . DIRECTORY_SEPARATOR . "uploads");
        }
        if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-files") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-file")) {
            mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-files");
        }
        if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-files" . DIRECTORY_SEPARATOR . $fileName)) {
            if (move_uploaded_file($_FILES['fileName']['tmp_name'], UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-files" . DIRECTORY_SEPARATOR . $fileName)) {
                $db->delete('1');
                $objPHPExcel = \PHPExcel_IOFactory::load(UPLOAD_PATH . DIRECTORY_SEPARATOR . "import-files" . DIRECTORY_SEPARATOR . $fileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $highestColumm = $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumm);
                $rownumber = 1;
                $row = $objPHPExcel->getActiveSheet()->getRowIterator($rownumber)->current();
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $header = explode(":", $cell->getValue());
                    $headerName[] = end($header);
                }
                $count = count($sheetData);
                $findInstancePosition = array_search('instanceID', $headerName);
                $findStartPosition = array_search('start', $headerName);
                $findEndPosition = array_search('end', $headerName);
                $findAuditSignPosition = array_search('auditorSignature', $headerName);
                $findAssesmentOfAuditPosition = array_search('assesmentofaudit', $headerName);

                for ($i = 2; $i <= $count; $i++) {
                    $row = $objPHPExcel->getActiveSheet()->getRowIterator($i)->current();
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $inc = 0;
                    $validateData = 0;
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        if ($inc == $findStartPosition || $inc == $findEndPosition && trim($cell->getValue()) != '') {
                            $dValue = explode(" ", trim($cell->getValue()));
                            if (count($dValue) == 2) {
                                $value = trim($cell->getValue());
                            } else {
                                $originalDate = $dValue[5] . "-" . $dValue[1] . "-" . $dValue[2];
                                $newDate = date("Y-m-d", strtotime($originalDate));
                                $value = $newDate . "T" . $dValue[3] . ".000+02";
                            }
                        }
                        if ($inc == $findAssesmentOfAuditPosition) {
                            if (trim($cell->getValue()) != '') {
                                $dValue = explode(" ", trim($cell->getValue()));
                                if (count($dValue) == 2) {
                                    $value = trim($cell->getValue());
                                } else {
                                    $originalDate = $dValue[5] . "-" . $dValue[1] . "-" . $dValue[2];
                                    $newDate = date("Y-m-d", strtotime($originalDate));
                                    $value = $newDate;
                                }
                            } else {
                                $value = '0000:00:00';
                            }
                        } else if ($inc == $findAuditSignPosition && trim($cell->getValue() != '')) {
                            $auditorSign = array('url' => $cell->getValue());
                            $value = json_encode($auditorSign);
                        }
                        if ($inc == $findInstancePosition) {
                            $validateQuery = $sql->select()->from(array('spiv3' => 'spi_form_v_3'))->where(array('instanceID' => trim($cell->getValue())));
                            $validateQueryStr = $sql->buildSqlString($validateQuery);
                            $validateResult = $dbAdapter->query($validateQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                            if ($validateResult) {
                                $validateData = 1; //exist meta instance id
                            } else {
                                $validateData = 0; //new meta instance id
                            }
                        }
                        $spiv3FormData[$headerName[$inc]] = $value;
                        $inc++;
                    }
                    $spiv3FormData['spi_data_status'] = $validateData;
                    $db->insert($spiv3FormData);
                }
            }
        }
    }
    public function getAllValidateSpiv3Details($params)
    {
        $db = $this->sm->get('SpiFormVer3TempTable');
        return $db->fetchAllValidateSpiv3Details($params);
    }
    public function addValidateSpiv3Data($params)
    {
        $db = $this->sm->get('SpiFormVer3Table');
        return $db->addValidateSpiv3Data($params);
    }

    //dashboard version 5

    public function getPerformanceV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getPerformanceV5($params);
    }

    public function getPerformanceLast30DaysV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getPerformanceLast30DaysV5($params);
    }

    public function getPerformanceLast180DaysV5($params = null)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getPerformanceLast180DaysV5();
    }

    public function getAllApprovedSubmissionsV5($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllApprovedSubmissionsV5($sortOrder);
    }

    public function getAllApprovedTestingVolumeV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getAllApprovedTestingVolumeV5($params);
    }

    public function getAllSubmissionsV5($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getAllSubmissionsV5($sortOrder);
    }

    //get all audit round no
    public function getSpiV5FormAuditNo()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchSpiV5FormAuditNo();
    }

    public function getSpiV5FormUniqueLevelNames()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchSpiV5FormUniqueLevelNames();
    }

    public function getAllTestingPointTypeV5()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllTestingPointTypeV5();
    }

    public function getZeroQuestionCountsV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getZeroQuestionCountsV5($params);
    }

    public function getSpiV5FormLabels()
    {
        $db = $this->sm->get('SpiForm5LabelsTable');
        return $db->getAllLabels();
    }

    public function getTestingPointTypeNamesByTypeV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchTestingPointTypeNamesByTypeV5($params);
    }

    //download spider chart pdf
    public function getAuditRoundWiseDataChartV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        $result = $db->getAuditRoundWiseData($params);
        $MyData = new Data();
        /* Create and populate the pData object */
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'], 2), round($adata['PHYSICAL_SCORE'], 2), round($adata['SAFETY_SCORE'], 2), round($adata['PRETEST_SCORE'], 2), round($adata['TEST_SCORE'], 2), round($adata['POST_SCORE'], 2), round($adata['EQA_SCORE'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);
        //$myPicture->drawGradientArea(0,0,450,50,DIRECTION_VERTICAL,array("StartR"=>400,"StartG"=>400,"StartB"=>400,"EndR"=>480,"EndG"=>480,"EndB"=>480,"Alpha"=>0));
        //$myPicture->drawGradientArea(0,0,450,25,DIRECTION_HORIZONTAL,array("StartR"=>60,"StartG"=>60,"StartB"=>60,"EndR"=>200,"EndG"=>200,"EndB"=>200,"Alpha"=>0));
        //$myPicture->drawLine(0,25,450,25,array("R"=>255,"G"=>255,"B"=>255));
        //$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */
        //$myPicture->setFontProperties(array("FontName"=>$path."/Silkscreen.ttf","FontSize"=>6));
        //$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "radar.png");
        return $fileName;
    }

    public function getAuditRoundWiseDataV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->getAuditRoundWiseDataV5($params);
    }

    public function getAllApprovedSubmissionLocationV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllApprovedSubmissionLocationV5($params);
    }

    public function updateSpiV5Form($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SpiFormVer5Table');
            $result = $db->updateSpiV5FormDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Form details updated successfully';
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    //get pending facility names v5
    public function getPendingFacilityNamesV5()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchPendingFacilityNames();
    }
    //get all facility names v5
    public function getAllFacilityNamesV5()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllFacilityNames();
    }

    public function getAllSubmissionsDatasV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDatas($params, $acl);
    }

    public function deleteAuditDataV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->deleteAuditRowData($params);
    }

    public function getFacilitiesAuditsV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchFacilitiesAudits($params);
    }

    //version 6

    public function getAllSpiV6TestingPointType()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllTestingPointTypeV6();
    }

    public function getAllSpiV6SubmissionsDetails($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDetails($params, $acl);
    }

    public function getAllV6DuplicateSubmissionsDetails()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllV6DuplicateSubmissionsDetails();
    }

    public function getV6DownloadFilesRow()
    {
        $db = $this->sm->get('SpiFormVer6DownloadTable');
        $result = $db->fetchDownloadFilesRow();
        $xlsx = new SimpleXLSXGen();
        $output = array();
        $headerRow = ['Audit Round No', 'Audit Date', 'Testing Point Type', 'Level', 'Affiliation', 'Audit Score'];
        $output[] = $headerRow;
        if(count($result) > 0){
            foreach($result as $data){
                $row = array();
                $row[] = trim($data['auditroundno']) == '' ? 'All' : $data['auditroundno'];;
                $row[] = $data['assesmentofaudit'];
                $row[] = trim($data['testingpointtype']) == '' ? 'All' : $data['testingpointtype'];
                $row[] = trim($data['level']) == '' ? 'All' : $data['level'];
                $row[] = trim($data['affiliation']) == '' ? 'All' : $data['affiliation'];
                $row[] = trim($data['AUDIT_SCORE_PERCENTAGE']) == '' ? 'All' : $data['AUDIT_SCORE_PERCENTAGE'];
                $output[] = $row;
            }
        }
        $xlsx->addSheet($output);
        $filename = 'Spi-v6-'.time().'.xlsx';
        return $xlsx->downloadAs($filename);
    }

    public function getAllV5DuplicateSubmissionsDetails()
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchAllV5DuplicateSubmissionsDetails();
    }

    public function removeAuditV5($params)
    {
        $db = $this->sm->get('SpiFormVer5DuplicateTable');
        return $db->removeAuditData($params);
    }

    public function removeAuditV6($params)
    {
        $db = $this->sm->get('SpiFormVer6DuplicateTable');
        return $db->removeAuditData($params);
    }

    public function approveSpiV6FormStatus($params)
    {
        //\Zend\Debug\Debug::dump(count($params['idList']));die;
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            //$facilityDb = $this->sm->get('SpiRt5FacilitiesTable');
            $facilityDb = $this->sm->get('SpiRtFacilitiesTable');
            $db = $this->sm->get('SpiFormVer6Table');
            if (isset($params['idList']) && $params['idList'] != '') {
                for ($i = 0; $i < count($params['idList']); $i++) {
                    $result = $db->updateFormStatus($params['idList'][$i], 'approved');
                    $facilityDb->addFacilityBasedOnForm($params['idList'][$i]);
                }
            }
            if (isset($params['id'])) {
                $result = $db->updateFormStatus($params['id'], 'approved');
                $facilityDb->addFacilityBasedOnForm($params['id']);
            }
            if ($result > 0) {
                $adapter->commit();
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getSpiV6FormData($id, $pdf = 'no')
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getFormData($id, $pdf);
    }

    public function exportSAndDV6Submissions($params)
    {
        try {
            $loginContainer = new Container('credo');
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $displayDate = "";
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date Range : " . $fromDate;
                } else {
                    $displayDate = "Date Range : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "Date Range : ";
            }
            $auditRndNo = '';
            $levelData = '';
            $affiliation = '';
            $province = '';
            $scoreLevel = '';
            $testPoint = '';
            if (isset($params['auditRndNo']) && ($params['auditRndNo'] != "")) {
                $auditRndNo = "Audit Round No. : " . $params['auditRndNo'];
            } else {
                $auditRndNo = "Audit Round No. : ";
            }
            if (isset($params['level']) && ($params['level'] != "")) {
                $levelData = "Level : " . $params['level'];
            } else {
                $levelData = "Level : ";
            }
            if (isset($params['affiliation']) && ($params['affiliation'] != "")) {
                $affiliation = "Affiliation : " . $params['affiliation'];
            } else {
                $affiliation = "Affiliation : ";
            }
            if (isset($params['province']) && ($params['province'] != "")) {
                $province = "Province/District(s) : " . implode(',', $params['province']);
            } else {
                $province = "Province/District(s) : ";
            }
            if (isset($params['scoreLevel']) && ($params['scoreLevel'] != "")) {
                $scoreLevel = "Score Level : " . $params['scoreLevel'];
            } else {
                $scoreLevel = "Score Level : ";
            }
            if (isset($params['testPoint']) && ($params['testPoint'] != "")) {
                $testPoint = "Type of Testing Point : " . $params['testPoint'];
            } else {
                $testPoint = "Type of Testing Point : ";
            }

            $startDate = "";
            $endDate = "";
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateField = explode(" ", $params['dateRange']);
                if (isset($dateField[0]) && trim($dateField[0]) != "") {
                    $startDate = \Application\Service\CommonService::isoDateFormat($dateField[0]);
                }
                if (isset($dateField[2]) && trim($dateField[2]) != "") {
                    $endDate = \Application\Service\CommonService::isoDateFormat($dateField[2]);
                }
            }

            $sQuery = $sql->select()->from(array('spiv6' => 'spi_form_v_6'))
                ->columns(array('formId', 'formVersion', 'meta-instance-id', 'meta-model-version', 'meta-ui-version', 'meta-submission-date', 'meta-is-complete', 'meta-date-marked-as-complete', 'start', 'end', 'today', 'deviceid', 'assesmentofaudit', 'auditEndTime', 'auditStartTime', 'auditroundno', 'facility', 'facilityname', 'testingpointtype', 'testingpointtype_other', 'physicaladdress', 'level', 'level_other', 'affiliation', 'affiliation_other', 'NumberofTester', 'AUDIT_SCORE_PERCENTAGE', 'AUDIT_SCORE_PERCANTAGE_ROUNDED', 'DO_SURVEILLANCE', 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY', 'S0_C_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY', 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL', 'S0_C_2_COUNSELORS_FOLLOWING_PROTOCOL', 'S0_Q_3_TESTS_RECORDED_RECENCY', 'S0_C_3_TESTS_RECORDED_RECENCY', 'S0_Q_4_PROCESS_DOCUMENTED', 'S0_C_4_PROCESS_DOCUMENTED', 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS', 'S0_C_5_RESULTS_RETURNED_IN_TWO_WEEKS', 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED', 'S0_C_6_PROTOCOL_VIOLATION_DOCUMENTED', 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS', 'S0_C_7_DOCUMENTING_PROTOCOL_ERRORS', 'D0_N_1_DIAGNOSED_HIV_ABOVE_15', 'D0_D_1_DIAGNOSED_HIV_ABOVE_15', 'D0_S_1_DIAGNOSED_HIV_ABOVE_15', 'D0_N_2_CANDIDATE_SCREENED_FOR_PARTICIPATION', 'D0_D_2_CANDIDATE_SCREENED_FOR_PARTICIPATION', 'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION', 'D0_N_3_ELIGIBLE_DURING_REVIEW_PERIOD', 'D0_D_3_ELIGIBLE_DURING_REVIEW_PERIOD', 'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD', 'D0_N_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD', 'D0_D_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD', 'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD', 'D0_N_5_DOCUMENTED_AND_REFUSED', 'D0_D_5_DOCUMENTED_AND_REFUSED', 'D0_S_5_DOCUMENTED_AND_REFUSED', 'D0_N_6_PARTICIAPANTS_ENROLLED_IN_RTRI', 'D0_D_6_PARTICIAPANTS_ENROLLED_IN_RTRI', 'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI', 'D0_N_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI', 'D0_D_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI', 'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI', 'D0_N_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI', 'D0_D_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI', 'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'))
                ->where('spiv6.status != "deleted"');

            if ($params['auditRndNo'] != '') {
                $sQuery = $sQuery->where("spiv6.auditroundno='" . $params['auditRndNo'] . "'");
            }
            if (trim($startDate) != "" && trim($endDate) != "") {
                $sQuery = $sQuery->where(array("spiv6.assesmentofaudit >='" . $startDate . "'", "spiv6.assesmentofaudit <='" . $endDate . "'"));
            }
            if ($params['level'] != '') {
                $sQuery = $sQuery->where("spiv6.level='" . $params['level'] . "'");
            }
            if ($params['affiliation'] != '') {
                $sQuery = $sQuery->where("spiv6.affiliation='" . $params['affiliation'] . "'");
            }

            if (isset($params['scoreLevel']) && $params['scoreLevel'] != '') {
                if ($params['scoreLevel'] == 0) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE < 40");
                } else if ($params['scoreLevel'] == 1) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 40 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 59");
                } else if ($params['scoreLevel'] == 2) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 60 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 79");
                } else if ($params['scoreLevel'] == 3) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 80 AND spiv6.AUDIT_SCORE_PERCENTAGE <= 89");
                } else if ($params['scoreLevel'] == 4) {
                    $sQuery = $sQuery->where("spiv6.AUDIT_SCORE_PERCENTAGE >= 90");
                }
            }
            if (isset($loginContainer->token) && !empty($loginContainer->token)) {
                $sQuery = $sQuery->where('spiv6.token IN ("' . implode('", "', $loginContainer->token) . '")');
            }
            if (isset($sWhere) && $sWhere != "") {
                $sQuery->where($sWhere);
            }

            if (isset($sOrder) && $sOrder != "") {
                $sQuery->order($sOrder);
            }
            $sQueryStr = $sql->buildSqlString($sQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

            if (count($sResult) > 0) {
                $auditScore = 0;
                $levelZero = array();
                $levelOne = array();
                $levelTwo = array();
                $levelThree = array();
                $levelFour = array();
                for ($l = 0; $l < count($sResult); $l++) {
                    $row = array();
                    $cells = array();
                    foreach ($sResult[$l] as $key => $aRow) {
                        if ($key != 'id' && $key != 'content' && $key != 'token') {
                            if ($key == 'AUDIT_SCORE_PERCENTAGE') {
                                if (!isset($sResult[$l][$key]) || !is_numeric($sResult[$l][$key])) {
                                    continue;
                                }

                                $auditScore += $sResult[$l][$key];
                                if ($sResult[$l][$key] < 40) {
                                    $levelZero[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 40 && $sResult[$l][$key] < 60) {
                                    $levelOne[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 60 && $sResult[$l][$key] < 80) {
                                    $levelTwo[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 80 && $sResult[$l][$key] < 90) {
                                    $levelThree[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 90) {
                                    $levelFour[] = $sResult[$l][$key];
                                }
                            }
                            if ($key == 'level_other') {
                                $level = " - " . $sResult[$l][$key];
                            } else {
                                $level = '';
                            }
                            if ($key == 'today') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            } else if ($key == 'assesmentofaudit') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            }
                            $row[] = $sResult[$l][$key] . $level;
                        }
                    }
                    $output[] = $row;
                }
                $outputScore['avgAuditScore'] = (count($sResult) > 0) ? round($auditScore / count($sResult), 2) : 0;
                $outputScore['levelZeroCount'] = count($levelZero);
                $outputScore['levelOneCount'] = count($levelOne);
                $outputScore['levelTwoCount'] = count($levelTwo);
                $outputScore['levelThreeCount'] = count($levelThree);
                $outputScore['levelFourCount'] = count($levelFour);
            }

            $fieldNames = array();
            $lastColumnArray = array();
            foreach ($outputScore as $key => $aRow) {
                $lastColumnArray[] = $key;
            }
            foreach ($sResult[0] as $key => $aRow) {
                if ($key != 'id' && $key != 'content' && $key != 'token') {
                    $fieldNames[] = $key;
                }
            }
            $xlsx = new SimpleXLSXGen();
            $outputData = array();
            $headerRow = ['Facility Report SPI-RT--CHECKLIST-version-6-S-AND-D-SECTION'];
            $outputData[] = $headerRow;
            $data = [$displayDate, $auditRndNo, $levelData, $affiliation, $scoreLevel, $testPoint];
            $outputData[] = $data;
            $outputData[] = $fieldNames;
            foreach ($output as $rowNo => $rowData) {
                $row = array();
                $colNo = 1;
                
                foreach ($rowData as $field => $value) {
                    if (!isset($value) || empty($value)) {
                        $value = "";
                    }
                    $row[] = $value;
                    $colNo++;
                }
                
                $outputData[] = $row;
            }

            $outputData[] = ['No.of Audit(s)    : '.count($sResult)];
            $outputData[] = ['Avg. Audit Score    : '.$outputScore['avgAuditScore']];

            $outputData[] = ['Level 0(Below 40) : '. $outputScore['levelZeroCount']];
            $outputData[] = ['Level 1(40-59)    : '.$outputScore['levelOneCount']];
            $outputData[] = ['Level 2(60-79)    : '.$outputScore['levelTwoCount']];
            $outputData[] = ['Level 3(80-89)    : '.$outputScore['levelThreeCount']];
            $outputData[] = ['Level 4(90)       : '.$outputScore['levelFourCount']];

            $xlsx->addSheet($outputData);
            $customTempFolderPath = TEMP_UPLOAD_PATH;
            $filename = 'SPI-RRT--CHECKLIST-version-6-S-AND-D-SECTION-' . time() . '.xlsx';
            $TemporaryFolderPath = $customTempFolderPath . DIRECTORY_SEPARATOR . $filename;
            $xlsx->mergeCells('A1:Q1');
            $xlsx->saveAs($TemporaryFolderPath);
            return $filename;
        } catch (\Exception $exc) {
            error_log("SPI-RT--CHECKLIST-version-6-S-AND-D-SECTION-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
            return "";
        }
    }

    public function exportAllV6Submissions($params)
    {
        try {
            $queryContainer = new Container('query');
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $displayDate = "";
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date Range : " . $fromDate;
                } else {
                    $displayDate = "Date Range : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "Date Range : ";
            }
            $auditRndNo = '';
            $levelData = '';
            $affiliation = '';
            $province = '';
            $scoreLevel = '';
            $testPoint = '';
            if (isset($params['auditRndNo']) && ($params['auditRndNo'] != "")) {
                $auditRndNo = "Audit Round No. : " . $params['auditRndNo'];
            } else {
                $auditRndNo = "Audit Round No. : ";
            }
            if (isset($params['level']) && ($params['level'] != "")) {
                $levelData = "Level : " . $params['level'];
            } else {
                $levelData = "Level : ";
            }
            if (isset($params['affiliation']) && ($params['affiliation'] != "")) {
                $affiliation = "Affiliation : " . $params['affiliation'];
            } else {
                $affiliation = "Affiliation : ";
            }
            if (isset($params['province']) && ($params['province'] != "")) {
                $province = "Province/District(s) : " . implode(',', $params['province']);
            } else {
                $province = "Province/District(s) : ";
            }
            if (isset($params['scoreLevel']) && ($params['scoreLevel'] != "")) {
                $scoreLevel = "Score Level : " . $params['scoreLevel'];
            } else {
                $scoreLevel = "Score Level : ";
            }
            if (isset($params['testPoint']) && ($params['testPoint'] != "")) {
                $testPoint = "Type of Testing Point : " . $params['testPoint'];
            } else {
                $testPoint = "Type of Testing Point : ";
            }
            $sQueryStr = $sql->buildSqlString($queryContainer->exportAllDataQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();

            if (count($sResult) > 0) {
                $auditScore = 0;
                $sQ1Score = 0;
                $sQ2Score = 0;
                $sQ3Score = 0;
                $sQ4Score = 0;
                $sQ5Score = 0;
                $sQ6Score = 0;
                $sQ7Score = 0;
                $D0_S1_Score = 0;
                $D0_S2_Score = 0;
                $D0_S3_Score = 0;
                $D0_S4_Score = 0;
                $D0_S5_Score = 0;
                $D0_S6_Score = 0;
                $D0_S7_Score = 0;
                $D0_S8_Score = 0;
                $levelZero = array();
                $levelOne = array();
                $levelTwo = array();
                $levelThree = array();
                $levelFour = array();
                for ($l = 0; $l < count($sResult); $l++) {
                    $row = array();
                    $cells = array();
                    foreach ($sResult[$l] as $key => $aRow) {
                        if ($key != 'id' && $key != 'content' && $key != 'token') {
                            if (($key == 'S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY')) {
                                $sQ1Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL')) {
                                $sQ2Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_3_TESTS_RECORDED_RECENCY')) {
                                $sQ3Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_4_PROCESS_DOCUMENTED')) {
                                $sQ4Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS')) {
                                $sQ5Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED')) {
                                $sQ6Score += $sResult[$l][$key];
                            }
                            if (($key == 'S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS')) {
                                $sQ7Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_1_DIAGNOSED_HIV_ABOVE_15')) {
                                $D0_S1_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION')) {
                                $D0_S2_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD')) {
                                $D0_S3_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD')) {
                                $D0_S4_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_5_DOCUMENTED_AND_REFUSED')) {
                                $D0_S5_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI')) {
                                $D0_S6_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI')) {
                                $D0_S7_Score += $sResult[$l][$key];
                            }
                            if (($key == 'D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI')) {
                                $D0_S8_Score += $sResult[$l][$key];
                            }
                            if ($key == 'AUDIT_SCORE_PERCENTAGE') {
                                if (!isset($sResult[$l][$key]) || !is_numeric($sResult[$l][$key])) {
                                    continue;
                                }

                                $auditScore += $sResult[$l][$key];
                                if ($sResult[$l][$key] < 40) {
                                    $levelZero[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 40 && $sResult[$l][$key] < 60) {
                                    $levelOne[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 60 && $sResult[$l][$key] < 80) {
                                    $levelTwo[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 80 && $sResult[$l][$key] < 90) {
                                    $levelThree[] = $sResult[$l][$key];
                                } else if ($sResult[$l][$key] >= 90) {
                                    $levelFour[] = $sResult[$l][$key];
                                }
                            }
                            if ($key == 'level_other') {
                                $level = " - " . $sResult[$l][$key];
                            } else {
                                $level = '';
                            }
                            if ($key == 'today') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            } else if ($key == 'assesmentofaudit') {
                                $sResult[$l][$key] = \Application\Service\CommonService::humanReadableDateFormat($sResult[$l][$key]);
                            }
                            $row[] = $sResult[$l][$key] . $level;
                        }
                    }
                    $output[] = $row;
                }
                $outputScore['avgAuditScore'] = (count($sResult) > 0) ? round($auditScore / count($sResult), 2) : 0;
                $outputScore['avgAuditScore'] = round($outputScore['avgAuditScore']);
                $outputScore['sQ1Score'] = (count($sResult) > 0) ? round((float) $sQ1Score / count($sResult), 2) : 0;
                $outputScore['sQ2Score'] = (count($sResult) > 0) ? round((float) $sQ2Score / count($sResult), 2) : 0;
                $outputScore['sQ3Score'] = (count($sResult) > 0) ? round((float) $sQ3Score / count($sResult), 2) : 0;
                $outputScore['sQ4Score'] = (count($sResult) > 0) ? round((float) $sQ4Score / count($sResult), 2) : 0;
                $outputScore['sQ5Score'] = (count($sResult) > 0) ? round((float) $sQ5Score / count($sResult), 2) : 0;
                $outputScore['sQ6Score'] = (count($sResult) > 0) ? round((float) $sQ6Score / count($sResult), 2) : 0;
                $outputScore['sQ7Score'] = (count($sResult) > 0) ? round((float) $sQ7Score / count($sResult), 2) : 0;
                $outputScore['D0_S1_Score'] = (count($sResult) > 0) ? round((float) $D0_S1_Score / count($sResult), 2) : 0;
                $outputScore['D0_S2_Score'] = (count($sResult) > 0) ? round((float) $D0_S2_Score / count($sResult), 2) : 0;
                $outputScore['D0_S3_Score'] = (count($sResult) > 0) ? round((float) $D0_S3_Score / count($sResult), 2) : 0;
                $outputScore['D0_S4_Score'] = (count($sResult) > 0) ? round((float) $D0_S4_Score / count($sResult), 2) : 0;
                $outputScore['D0_S5_Score'] = (count($sResult) > 0) ? round((float) $D0_S5_Score / count($sResult), 2) : 0;
                $outputScore['D0_S6_Score'] = (count($sResult) > 0) ? round((float) $D0_S6_Score / count($sResult), 2) : 0;
                $outputScore['D0_S7_Score'] = (count($sResult) > 0) ? round((float) $D0_S7_Score / count($sResult), 2) : 0;
                $outputScore['D0_S8_Score'] = (count($sResult) > 0) ? round((float) $D0_S8_Score / count($sResult), 2) : 0;
                $outputScore['levelZeroCount'] = count($levelZero);
                $outputScore['levelOneCount'] = count($levelOne);
                $outputScore['levelTwoCount'] = count($levelTwo);
                $outputScore['levelThreeCount'] = count($levelThree);
                $outputScore['levelFourCount'] = count($levelFour);

            }
            $fieldNames = array();
            $lastColumnArray = array();
            foreach ($outputScore as $key => $aRow) {
                $lastColumnArray[] = $key;
            }
            foreach ($sResult[0] as $key => $aRow) {
                if ($key != 'id' && $key != 'content' && $key != 'token') {
                    $fieldNames[] = $key;
                }
            }
            $xlsx = new SimpleXLSXGen();
            $outputData = array();
            $headerRow = ['Facility Report SPI-RRT--CHECKLIST-version-6'];
            $outputData[] = $headerRow;
            $data = [$displayDate, $auditRndNo, $levelData, $affiliation, $scoreLevel, $testPoint];
            $outputData[] = $data;
            $outputData[] = $fieldNames;
            foreach ($output as $rowNo => $rowData) {
                $row = array();
                $colNo = 1;
                
                foreach ($rowData as $field => $value) {
                    if (!isset($value) || empty($value)) {
                        $value = "";
                    }
                    
                    $row[] = $value;
                    $colNo++;
                }
                
                $outputData[] = $row;
            }
            $outputData[] = ['No.of Audit(s)    : '.count($sResult)];
            $outputData[] = ['Avg. Audit Score    : '.$outputScore['avgAuditScore']];

            $outputData[] = ['Avg. S0_Q_1_SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY Score    : '.$outputScore['sQ1Score']];
            $outputData[] = ['Avg. S0_Q_2_COUNSELORS_FOLLOWING_PROTOCOL Score    : '. $outputScore['sQ2Score']];
            $outputData[] = ['Avg. S0_Q_3_TESTS_RECORDED_RECENCY Score    : '.$outputScore['sQ3Score']];
            $outputData[] = ['Avg. S0_Q_4_PROCESS_DOCUMENTED Score    : '.$outputScore['sQ4Score']];
            $outputData[] = ['Avg. S0_Q_5_RESULTS_RETURNED_IN_TWO_WEEKS Score    : '.$outputScore['sQ5Score']];
            $outputData[] = ['Avg. S0_Q_6_PROTOCOL_VIOLATION_DOCUMENTED Score    : '.$outputScore['sQ6Score']];
            $outputData[] = ['Avg. S0_Q_7_DOCUMENTING_PROTOCOL_ERRORS Score    : '.$outputScore['sQ7Score']];

            $outputData[] = ['Avg. D0_S_1_DIAGNOSED_HIV_ABOVE_15 Score    : '.$outputScore['D0_S1_Score']];
            $outputData[] = ['Avg. D0_S_2_CANDIDATE_SCREENED_FOR_PARTICIPATION Score    : '.$outputScore['D0_S2_Score']];
            $outputData[] = ['Avg. D0_S_3_ELIGIBLE_DURING_REVIEW_PERIOD Score    : '.$outputScore['D0_S3_Score']];
            $outputData[] = ['Avg. D0_S_4_ELIGIBLE_AND_DECLINED_REVIEW_PERIOD Score    : '.$outputScore['D0_S4_Score']];
            $outputData[] = ['Avg. D0_S_5_DOCUMENTED_AND_REFUSED Score    : '.$outputScore['D0_S5_Score']];
            $outputData[] = ['Avg. D0_S_6_PARTICIAPANTS_ENROLLED_IN_RTRI Score    : '.$outputScore['D0_S6_Score']];
            $outputData[] = ['Avg. D0_S_7_PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI Score    : '.$outputScore['D0_S7_Score']];
            $outputData[] = ['Avg. D0_S_8_PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI Score    : '.$outputScore['D0_S8_Score']];

            $outputData[] = ['Level 0(Below 40) : '.$outputScore['levelZeroCount']];
            $outputData[] = ['Level 1(40-59)    : '.$outputScore['levelOneCount']];
            $outputData[] = ['Level 2(60-79)    : '.$outputScore['levelTwoCount']];
            $outputData[] = ['Level 3(80-89)    : '.$outputScore['levelThreeCount']];
            $outputData[] = ['Level 4(90)       : '.$outputScore['levelFourCount']];

            $xlsx->addSheet($outputData);
            $customTempFolderPath = TEMP_UPLOAD_PATH;
            $filename = 'SPI-RRT--CHECKLIST-version-6-' . time() . '.xlsx';
            $TemporaryFolderPath = $customTempFolderPath . DIRECTORY_SEPARATOR . $filename;
            $xlsx->mergeCells('A1:Q1');
            $xlsx->saveAs($TemporaryFolderPath);
            return $filename;
        } catch (\Exception $exc) {
            error_log("SPI-RRT--CHECKLIST-version-6-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());

            return "";
        }
    }

    public function getSpiV6PerformancePieChart($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getPerformanceV6($params);
        // echo "Prasath";die;
        $MyData = new Data();
        if (count($result) > 0) {
            foreach ($result as $key => $data) {
                $MyData->addPoints(array($data['level0'], $data['level1'], $data['level2'], $data['level3'], $data['level4']), "Level" . $key);
                $MyData->setSerieDescription("Level" . $key);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Level" . $key, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }

        $percentage = $result[0]['level0'] + $result[0]['level1'] + $result[0]['level2'] + $result[0]['level3'] + $result[0]['level4'];

        /* Define the absissa serie */
        $MyData->addPoints(
            array(
                $this->translator->translate('Level 0 (Below 40)') . "&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level0'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level0'] . ")",
                $this->translator->translate('Level 1 (40-59)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level1'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level1'] . ")",
                $this->translator->translate('Level 2 (60-79)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level2'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level2'] . ")",
                $this->translator->translate('Level 3 (80-89)') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . round(($result[0]['level3'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level3'] . ")",
                $this->translator->translate('Level 4 (90 and above)') . "&nbsp;" . round(($result[0]['level4'] / $percentage) * 100, 1) . "%&nbsp;(" . $this->translator->translate('No. of Audits') . "&nbsp;" . $result[0]['level4'] . ")",
            ),
            "Labels"
        );
        $MyData->setAbscissa("Labels");

        /* Create the pChart object */
        $myPicture = new Image(400, 510, $MyData);
        $myPicture->drawRectangle(0, 0, 390, 480, array("R" => 0, "G" => 0, "B" => 0));
        $path = FONT_PATH . DIRECTORY_SEPARATOR;

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 13, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 0, "Y" => 0, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 0));

        $PieChart = new Pie($myPicture, $MyData);
        $PieChart->draw2DPie(195, 195, array("Radius" => 190, "Border" => true));
        $PieChart->drawPieLegend(5, 390);
        $fileName = 'piechart-spiv6.png';
        $myPicture->drawText(540, 200, "Extended AA pass / Splitted", ["R" => 0, "G" => 0, "B" => 0, "Align" => TEXT_ALIGN_TOPMIDDLE]);
        $fileName = 'piechart-spiv6.png';
        $PieChart->pChartObject->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);

        //header('Content-Type: text/plain');
        //var_dump($path);die;
        //$result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "piechart-spiv5.png");
        return $fileName;
    }

    public function getSpiV6AuditRoundWiseDataChart($params)
    {

        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseDataV6($params);
        $MyData = new Data();
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'], 2), round($adata['PHYSICAL_SCORE'], 2), round($adata['SAFETY_SCORE'], 2), round($adata['PRETEST_SCORE'], 2), round($adata['TEST_SCORE'], 2), round($adata['POST_SCORE'], 2), round($adata['EQA_SCORE'], 2), round($adata['RTRI_SCORE'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit", "RTRT Surveillance"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);
        //$myPicture->drawGradientArea(0,0,450,50,DIRECTION_VERTICAL,array("StartR"=>400,"StartG"=>400,"StartB"=>400,"EndR"=>480,"EndG"=>480,"EndB"=>480,"Alpha"=>0));
        //$myPicture->drawGradientArea(0,0,450,25,DIRECTION_HORIZONTAL,array("StartR"=>60,"StartG"=>60,"StartB"=>60,"EndR"=>200,"EndG"=>200,"EndB"=>200,"Alpha"=>0));
        //$myPicture->drawLine(0,25,450,25,array("R"=>255,"G"=>255,"B"=>255));
        //$RectangleSettings = array("R"=>180,"G"=>180,"B"=>180,"Alpha"=>50);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */
        //$myPicture->setFontProperties(array("FontName"=>$path."/Silkscreen.ttf","FontSize"=>6));
        //$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));
        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-spiv6.png';
        //print_r($fileName);die;
        $result = $myPicture->render(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    public function getSpiV6AuditRoundWiseS0DataChart($params)
    {

        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseS0DataV6($params);
        $MyData = new Data();
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'], 2), round($adata['COUNSELORS_FOLLOWING_PROTOCOL'], 2), round($adata['TESTS_RECORDED_RECENCY'], 2), round($adata['PROCESS_DOCUMENTED'], 2), round($adata['RESULTS_RETURNED_IN_TWO_WEEKS'], 2), round($adata['PROTOCOL_VIOLATION_DOCUMENTED'], 2), round($adata['DOCUMENTING_PROTOCOL_ERRORS'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance " . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop., round($adata['RTRI_SCORE'], , round($adata['RTRI_SCORE'], 2)2)
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array(
            "Surveilance Eligibility",
            "Counselor Protocol",
            "Recency Test Recorded",
            "Process Documented",
            "2 weeks Results",
            "Violation Documented",
            "Documented Errors",
        ), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));
        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-section-s0-spiv6.png';
        //print_r($fileName);die;
        $result = $myPicture->render(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    public function getSpiV6AuditRoundWiseD0DataChart($params)
    {

        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseD0DataV6($params);
        $MyData = new Data();
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {
                //$MyData->addPoints(array(round($adata['PERSONAL_SCORE'],2),round($adata['PHYSICAL_SCORE'],2),round($adata['SAFETY_SCORE'],2),round($adata['PRETEST_SCORE'],2),round($adata['TEST_SCORE'],2),round($adata['POST_SCORE'],2),round($adata['EQA_SCORE'],2)),"Score".$auditNo);
                $MyData->addPoints(array(round($adata['DIAGNOSED_HIV_ABOVE_15'], 2), round($adata['CANDIDATE_SCREENED_FOR_PARTICIPATION'], 2), round($adata['ELIGIBLE_DURING_REVIEW_PERIOD'], 2), round($adata['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'], 2), round($adata['DOCUMENTED_AND_REFUSED'], 2), round($adata['PARTICIAPANTS_ENROLLED_IN_RTRI'], 2), round($adata['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'], 2), round($adata['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance " . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop., round($adata['RTRI_SCORE'], , round($adata['RTRI_SCORE'], 2)2)
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array(
            "HIV above 15",
            "Screened for participation",
            "Eligible during Review",
            "Eligible and declined",
            "Documented and refused",
            "Enrolled in RTRI",
            "Incorrectly Enrolled",
            "Correctly Enrolled",
        ), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));
        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-section-d0-spiv6.png';
        //print_r($fileName);die;
        $result = $myPicture->render(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    public function addV6DownloadData($params)
    {
        // print_r($params);die;
        $db = $this->sm->get('SpiFormVer6DownloadTable');
        return $db->addDownloadDataDetails($params);
    }

    public function getPerformanceV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getPerformanceV6($params);
    }

    public function getPerformanceLast30DaysV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getPerformanceLast30DaysV6($params);
    }

    public function getPerformanceLast180DaysV6($params = null)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getPerformanceLast180DaysV6();
    }

    public function getAllApprovedSubmissionsV6($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllApprovedSubmissionsV6($sortOrder);
    }

    public function getHighVolumeSites()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getHighVolumeSites();
    }

    public function getAllApprovedTestingVolumeV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAllApprovedTestingVolumeV6($params);
    }

    public function getAllSubmissionsV6($sortOrder = 'DESC')
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAllSubmissionsV6($sortOrder);
    }

    //get all audit round no
    public function getSpiV6FormAuditNo()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchSpiV6FormAuditNo();
    }

    public function getSpiV6FormUniqueLevelNames()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchSpiV6FormUniqueLevelNames();
    }

    public function getAllTestingPointTypeV6()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllTestingPointTypeV6();
    }

    public function updateSpiV6Form($params)
    {
        $adapter = $this->sm->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SpiFormVer6Table');
            $result = $db->updateSpiV6FormDetails($params);
            if ($result > 0) {
                $adapter->commit();
                $container = new Container('alert');
                $container->alertMsg = 'Form details updated successfully';
                return $result;
            }
        } catch (\Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllSubmissionsDatasV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSubmissionsDatas($params, $acl);
    }

    public function deleteAuditDataV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->deleteAuditRowData($params);
    }

    public function getZeroQuestionCountsV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getZeroQuestionCountsV6($params);
    }

    public function getSpiV6FormLabels()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAllLabels();
    }

    public function getAuditRoundWiseDataV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAuditRoundWiseDataV6($params);
    }

    // Roundwise Audit Data for Section S0

    public function getAuditRoundWiseSectionS0DataV6($params)
    {
        //echo "ss";die;
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAuditRoundWiseS0DataV6($params);
    }

    // Roundwise Audit Data for Section D0

    public function getAuditRoundWiseSectionD0DataV6($params)
    {
        //echo "ss";die;
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getAuditRoundWiseD0DataV6($params);
    }

    public function getAllApprovedSubmissionLocationV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllApprovedSubmissionLocationV6($params);
    }

    //download spider chart pdf
    public function getAuditRoundWiseDataChartV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseDataV6($params);
        $MyData = new Data();
        /* Create and populate the pData object */
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {

                $MyData->addPoints(array(round($adata['PERSONAL_SCORE'], 2), round($adata['PHYSICAL_SCORE'], 2), round($adata['SAFETY_SCORE'], 2), round($adata['PRETEST_SCORE'], 2), round($adata['TEST_SCORE'], 2), round($adata['POST_SCORE'], 2), round($adata['EQA_SCORE'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Personnel Training & Certification", "Physical", "Safety", "Pre-Testing", "Testing", "Post Testing Phase", "External Quality Audit"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . "radar.png");
        return $fileName;
    }

    /*
    get section S0 V6
     */

    public function getAuditRoundWiseS0DataChartV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseS0DataV6($params);
        $MyData = new Data();
        /* Create and populate the pData object */
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {

                $MyData->addPoints(array(round($adata['SURVEILLANCE_STUDY_PROTOCOL_ELIGIBILITY'], 2), round($adata['COUNSELORS_FOLLOWING_PROTOCOL'], 2), round($adata['TESTS_RECORDED_RECENCY'], 2), round($adata['PROCESS_DOCUMENTED'], 2), round($adata['RESULTS_RETURNED_IN_TWO_WEEKS'], 2), round($adata['PROTOCOL_VIOLATION_DOCUMENTED'], 2), round($adata['DOCUMENTING_PROTOCOL_ERRORS'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("Surveilance Eligibility", "Counselor Protocol", "Recency Test Recorded", "Process Documented", "2 weeks Results", "Violation Documented", "Documented Errors"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-s0-v6.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    /*
    get section D0 V6
     */

    public function getAuditRoundWiseD0DataChartV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        $result = $db->getAuditRoundWiseD0DataV6($params);
        $MyData = new Data();
        /* Create and populate the pData object */
        $filename = '';
        if (count($result) > 0) {
            foreach ($result as $auditNo => $adata) {

                $MyData->addPoints(array(round($adata['DIAGNOSED_HIV_ABOVE_15'], 2), round($adata['CANDIDATE_SCREENED_FOR_PARTICIPATION'], 2), round($adata['ELIGIBLE_DURING_REVIEW_PERIOD'], 2), round($adata['ELIGIBLE_AND_DECLINED_REVIEW_PERIOD'], 2), round($adata['DOCUMENTED_AND_REFUSED'], 2), round($adata['PARTICIAPANTS_ENROLLED_IN_RTRI'], 2), round($adata['PARTICIAPANTS_INCORRECTLY_ENROLLED_IN_RTRI'], 2), round($adata['PARTICIAPANTS_CORRECTLY_ENROLLED_IN_RTRI'], 2)), "Audit Performance");
                $MyData->setSerieDescription("Audit Performance" . $auditNo, $auditNo);
                $rgbColor = array();
                //Create a loop.
                foreach (array('r', 'g', 'b') as $color) {
                    //Generate a random number between 0 and 255.
                    $rgbColor[$color] = mt_rand(0, 255);
                }
                $MyData->setPalette("Audit Performance" . $auditNo, array("R" => $rgbColor['r'], "G" => $rgbColor['g'], "B" => $rgbColor['b']));
            }
        }
        /* Define the absissa serie */
        $MyData->addPoints(array("HIV above 15", "Screened for participation", "Eligible during Review", "Eligible and declined", "Documented and refused", "Enrolled in RTRI", "Incorrectly Enrolled", "Correctly Enrolled"), "Label");
        $MyData->setAbscissa("Label");

        /* Create the pChart object */
        $myPicture = new Image(600, 690, $MyData);

        /* Add a border to the picture */
        $myPicture->drawRectangle(0, 0, 599, 678, array("R" => 0, "G" => 0, "B" => 0));

        $path = FONT_PATH . DIRECTORY_SEPARATOR;
        /* Write the picture title */

        /* Set the default font properties */
        $myPicture->setFontProperties(array("FontName" => $path . "/Forgotte.ttf", "FontSize" => 15, "R" => 80, "G" => 80, "B" => 80));

        /* Enable shadow computing */
        $myPicture->setShadow(true, array("X" => 1, "Y" => 1, "R" => 0, "G" => 0, "B" => 0, "Alpha" => 10));

        /* Create the pRadar object */
        $SplitChart = new Radar();
        /* Draw a radar chart */
        $myPicture->setGraphArea(15, 15, 590, 590);
        $Options = array("Layout" => RADAR_LAYOUT_STAR, "BackgroundGradient" => array("StartR" => 510, "StartG" => 510, "StartB" => 510, "StartAlpha" => 10, "EndR" => 414, "EndG" => 454, "EndB" => 250, "EndAlpha" => 10), "FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 15);
        $SplitChart->drawRadar($myPicture, $MyData, $Options);

        /* Write the chart legend */
        $myPicture->setFontProperties(array("FontName" => $path . "/pf_arma_five.ttf", "FontSize" => 7));
        $myPicture->drawLegend(330, 620, array("Style" => LEGEND_BOX, "Mode" => LEGEND_VERTICAL));

        /* Render the picture (choose the best way) */
        $fileName = 'radar-d0-v6.png';
        $result = $myPicture->autoOutput(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $fileName);
        return $fileName;
    }

    public function getTestingPointTypeNamesByTypeV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchTestingPointTypeNamesByTypeV6($params);
    }

    public function getViewDataDetailsV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchViewDataDetails($params);
    }

    public function getViewDataS0DetailsV6($params)
    {
        //echo "ss";die;
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchViewDataS0Details($params);
    }

    public function getViewDataD0DetailsV6($params)
    {
        //echo "ss";die;
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchViewDataD0Details($params);
    }

    public function getViewDataDetailsV5($params)
    {
        $db = $this->sm->get('SpiFormVer5Table');
        return $db->fetchViewDataDetails($params);
    }

    //get all facility names v6
    public function getAllFacilityNamesV6()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllFacilityNames();
    }

    public function getFacilitiesAuditsV6($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchFacilitiesAudits($params);
    }

    public function getAllApprovedV6FormSubmissionsTable($params)
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchAllApprovedV6FormSubmissionsTable($params);
    }

    public function getSpiV6PendingCount()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->getSpiV6PendingCount();
    }

    public function exportV6FacilityReport($params)
    {
        try {
            $common = new \Application\Service\CommonService();
            $queryContainer = new Container('query');
            $output = array();
            $dbAdapter = $this->sm->get('Laminas\Db\Adapter\Adapter');
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = 'facility-report-v6' . date('d-M-Y-H-i-s') . '.xlsx';
            //$writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            $sheet = $spreadsheet->getActiveSheet();
            $sql = new Sql($dbAdapter);
            if (isset($params['dateRange']) && ($params['dateRange'] != "")) {
                $dateRangeDate = explode(" - ", $params['dateRange']);
                if (isset($dateRangeDate[0]) && trim($dateRangeDate[0]) != "") {
                    $fromDate = $dateRangeDate[0];
                }
                if (isset($dateRangeDate[1]) && trim($dateRangeDate[1]) != "") {
                    $toDate = $dateRangeDate[1];
                }
                if ($fromDate == $toDate) {
                    $displayDate = "Date : " . $fromDate;
                } else {
                    $displayDate = "Date : " . $fromDate . " to " . $toDate;
                }
            } else {
                $displayDate = "";
            }

            $sQueryStr = $sql->buildSqlString($queryContainer->exportQuery);

            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            if (count($sResult) > 0) {

                foreach ($sResult as $aRow) {
                    $auditDate = "";
                    if (isset($aRow['assesmentofaudit']) && trim($aRow['assesmentofaudit']) != "") {
                        $auditDate = \Application\Service\CommonService::humanReadableDateFormat($aRow['assesmentofaudit']);
                    }
                    $row = array();
                    $row[] = $aRow['facilityname'];
                    $row[] = $auditDate;
                    $row[] = $aRow['testingpointtype'];
                    $row[] = $aRow['PERSONAL_SCORE'];
                    $row[] = $aRow['PHYSICAL_SCORE'];
                    $row[] = $aRow['SAFETY_SCORE'];
                    $row[] = $aRow['PRETEST_SCORE'];
                    $row[] = $aRow['TEST_SCORE'];
                    $row[] = $aRow['POST_SCORE'];
                    $row[] = $aRow['EQA_SCORE'];
                    $row[] = $aRow['RTRI_SCORE'];
                    $row[] = $aRow['FINAL_AUDIT_SCORE'];
                    $row[] = round($aRow['AUDIT_SCORE_PERCENTAGE'], 2);
                    $output[] = $row;
                }
            }
            //echo "<pre>";
            //print_r($output);die;
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
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    ),
                ),
            );

            $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    ),
                ),
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
            $sheet->mergeCells('I4:I5');
            $sheet->mergeCells('J4:J5');
            $sheet->mergeCells('K4:K5');
            $sheet->mergeCells('L4:L5');
            $sheet->mergeCells('M4:M5');

            $sheet->setCellValue('A1', html_entity_decode('Facility Report Version 6', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($displayDate, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->setCellValue('A4', html_entity_decode('Facility name', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Audit Date', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Testing Point', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Personnel Training & Certification', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Physical', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Safety', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Pre-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Post-Testing', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('External QA', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('RTRI', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('Total', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('% Scores', ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(13);

            $sheet->getStyle('A4:A5')->applyFromArray($styleArray);
            $sheet->getStyle('B4:B5')->applyFromArray($styleArray);
            $sheet->getStyle('C4:C5')->applyFromArray($styleArray);
            $sheet->getStyle('D4:D5')->applyFromArray($styleArray);
            $sheet->getStyle('E4:E5')->applyFromArray($styleArray);
            $sheet->getStyle('F4:F5')->applyFromArray($styleArray);
            $sheet->getStyle('G4:G5')->applyFromArray($styleArray);
            $sheet->getStyle('H4:H5')->applyFromArray($styleArray);
            $sheet->getStyle('I4:I5')->applyFromArray($styleArray);
            $sheet->getStyle('J4:J5')->applyFromArray($styleArray);
            $sheet->getStyle('K4:K5')->applyFromArray($styleArray);
            $sheet->getStyle('L4:L5')->applyFromArray($styleArray);
            $sheet->getStyle('M4:M5')->applyFromArray($styleArray);

            $start = 0;
            foreach ($output as $rowNo => $rowData) {
                $colNo = 1;
                foreach ($rowData as $field => $value) {

                    if (!isset($value)) {
                        $value = "";
                    }
                    //echo $colNo.'---'.$rowNo;die;
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 6;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 6)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 6)->getAlignment()->setWrapText(true);
                    ++$colNo;
                }
            }

            //$writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            //$filename = 'facility-report-' . date('d-M-Y-H-i-s') . '.xls';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            return $filename;
        } catch (\Exception $exc) {
            error_log("GENERATE-FACILITY-REPORT-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());

            return "";
        }
    }

    //get pending facility names v6
    public function getPendingFacilityNamesV6()
    {
        $db = $this->sm->get('SpiFormVer6Table');
        return $db->fetchPendingFacilityNames();
    }

    // Finding the odkformsubmissions

    public function syncOdkCentralV3()
    {
        $configResult = $this->sm->get('Config');
        $spirrtURL = $configResult['odkcentral']['spirt']['url'];
        $projectId = $configResult['odkcentral']['spirt']['projectId'];
        $formId = $configResult['odkcentral']['spirt']['formId'];

        $spiV3db = $this->sm->get('SpiFormVer3Table');
        $lastDateQuery = $spiV3db->getLatestFormDate();
        $lastFormDate = $lastDateQuery[0]["last_added_form_date"];
        $baseUrl = $spirrtURL . "/v1/projects/$projectId/forms/$formId";
        if ($lastFormDate != '') {
            $url = "$baseUrl.svc/Submissions?%24filter=__system%2FsubmissionDate%20gt%20$lastFormDate";
        } else {
            $url = "$baseUrl.svc/Submissions";
        }
        // $odataClient = new ODataClient($spirrtURL, function($request) {
        $email = $configResult['odkcentral']['spirt']['email'];
        $password = $configResult['odkcentral']['spirt']['password'];

        $httpClient = new Client([
            'base_uri' => $baseUrl,
            'cookies' => true,
        ]);

        // Authenticate and obtain session cookie
        $response = $httpClient->post('/v1/sessions', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        // Check if the request was successful
        if ($response->getStatusCode() == 200) {
            $authResponse = json_decode($response->getBody()->getContents(), true);
            $authToken = $authResponse['token'];
            // Fetch instanceIdList
            $response = $httpClient->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                ],
            ]);
            $instanceIdList = $response->getBody()->getContents();
            $responseSubmission = $this->formatResponse($instanceIdList);

            $instanceLists = [];
            $correctiveActions = [];

            foreach ($responseSubmission['value'] as $submission) {
                foreach ($submission as $key => $listValue) {
                    if ($key === '__id') {
                        $instanceLists[] = $listValue;

                        $formInstanceResponse = $httpClient->get("$baseUrl/submissions/$listValue.xml", [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $authToken,
                                'Content-Type' => 'application/xml',
                            ],
                        ]);
                        $formXml = ($formInstanceResponse->getBody()->getContents());
                        $xml = simplexml_load_string($formXml);
                        $json = json_encode($xml);
                        $array = json_decode($json, true);
                        $correctiveActions[$listValue] = isset($array['correctiveaction'][0]) ? $array['correctiveaction'] : array($array['correctiveaction']);
                    }
                }
            }

            $formResponse = $httpClient->get($baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                ],
            ]);
            $formDetails = ($formResponse->getBody()->getContents());
            $formDetails = $this->formatResponse($formDetails);


            if (isset($responseSubmission['value']) && !empty($responseSubmission['value'])) {
                $spiV3db->saveOdkCentralData($responseSubmission, $formDetails, $correctiveActions);
            }
        } else {
            echo "Error authenticating: " . $response->getStatusCode();
        }
    }

    public function syncOdkCentralV6()
    {
        $configResult = $this->sm->get('Config');
        $spirrtURL = $configResult['odkcentral']['spirrt']['url'];
        $projectId = $configResult['odkcentral']['spirrt']['projectId'];
        $formId = $configResult['odkcentral']['spirrt']['formId'];

        $spiV6db = $this->sm->get('SpiFormVer6Table');
        $lastDateQuery = $spiV6db->getLatestFormDate();
        $lastFormDate = $lastDateQuery[0]["last_added_form_date"];
        $baseUrl = $spirrtURL . "/v1/projects/$projectId/forms/$formId";
        if ($lastFormDate != '') {
            $url = "$baseUrl.svc/Submissions?%24filter=__system%2FsubmissionDate%20gt%20$lastFormDate";
        } else {
            $url = "$baseUrl.svc/Submissions";
        }
        // $odataClient = new ODataClient($spirrtURL, function($request) {
        $email = $configResult['odkcentral']['spirrt']['email'];
        $password = $configResult['odkcentral']['spirrt']['password'];

        $httpClient = new Client([
            'base_uri' => $baseUrl,
            'cookies' => true,
        ]);

        // Authenticate and obtain session cookie
        $response = $httpClient->post('/v1/sessions', [
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        // Check if the request was successful
        if ($response->getStatusCode() == 200) {
            $authResponse = json_decode($response->getBody()->getContents(), true);
            $authToken = $authResponse['token'];
            // Fetch instanceIdList
            $response = $httpClient->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                ],
            ]);
            $instanceIdList = $response->getBody()->getContents();
            $responseSubmission = $this->formatResponse($instanceIdList);

            $instanceLists = [];
            $correctiveActions = [];

            foreach ($responseSubmission['value'] as $submission) {
                foreach ($submission as $key => $listValue) {
                    if ($key === '__id') {
                        $instanceLists[] = $listValue;

                        $formInstanceResponse = $httpClient->get("$baseUrl/submissions/$listValue.xml", [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $authToken,
                                'Content-Type' => 'application/xml',
                            ],
                        ]);
                        $formXml = ($formInstanceResponse->getBody()->getContents());
                        $xml = simplexml_load_string($formXml);
                        $json = json_encode($xml);
                        $array = json_decode($json, true);
                        $correctiveActions[$listValue] = isset($array['correctiveaction'][0]) ? $array['correctiveaction'] : array($array['correctiveaction']);
                    }
                }
            }

            $formResponse = $httpClient->get($baseUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                ],
            ]);
            $formDetails = ($formResponse->getBody()->getContents());
            $formDetails = $this->formatResponse($formDetails);


            if (isset($responseSubmission['value']) && !empty($responseSubmission['value'])) {
                $spiV6db->saveOdkCentralData($responseSubmission, $formDetails, $correctiveActions);
            }
        } else {
            echo "Error authenticating: " . $response->getStatusCode();
        }
    }

    public function formatResponse($strResponse)
    {
        $response = html_entity_decode(stripslashes($strResponse), ENT_QUOTES, 'UTF-8');
        $obj = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response), true);
        return $obj;
    }
}
