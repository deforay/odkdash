<?php

namespace Application\Service;

use setasign\Fpdi\TcpdfFpdi;

class FpdiExtends extends TcpdfFpdi // Extend TcpdfFpdi, which includes TCPDF methods
{
    public $tempFile;
    public $tempTopMargin;
    public $template = "";

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
    }

    public function setParams($tempFile, $tempTopMargin)
    {
        $this->tempFile = $tempFile; // template file
        $this->tempTopMargin = $tempTopMargin; // tempTopMargin
        if (!empty($tempFile) && file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $tempFile)) {
            $this->template = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $tempFile;
        }
        if (isset($this->tempTopMargin) && !empty($this->tempTopMargin)) {
            $this->SetY($this->tempTopMargin - 10);
        } else {
            $this->SetY(32);
        }
    }

    public function Header()
    {
        // Check if the template file is set and exists
        if (!empty($this->template) && $this->template != "") {
            // Load the template file
            $this->setSourceFile($this->template);
            $template = $this->ImportPage(1);  // Import the first page (or whichever page has the header)

            // Get page dimensions to calculate full width and height
            $pageWidth = $this->getPageWidth();  // Full width of the PDF page
            $pageHeight = $this->getPageHeight();  // Height of the PDF page (not needed here, but can be useful)

            // Use the template and scale it to full page width
            $this->useImportedPage($template, 0, 0, $pageWidth); // Full width from 0 x-coordinate
        }

        // Optionally, set font and text if you want to add more elements after the template
        $this->SetFont('helvetica', 'B', 12);
        // Example of adding centered header text after the template
        // $this->Cell(0, 10, 'Centered Header Text', 0, 1, 'C');
    }


    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
