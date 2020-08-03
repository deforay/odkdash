<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class CronController extends AbstractActionController
{


    private $commonService = null;
    private $odkFormService = null;

    public function __construct($commonService, $odkFormService)
    {
        $this->commonService = $commonService;
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
    }

    public function sendMailAction()
    {

        $this->commonService->sendTempMail();
    }

    public function sendAuditMailAction()
    {

        $this->commonService->sendAuditMail();
    }

    public function dbBackupAction()
    {

        $this->commonService->dbBackup();
    }

    public function generateBulkPdfAction()
    {

        $this->odkFormService->getDownloadDataList();
    }
}
