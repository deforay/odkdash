<?php

namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Certification\Model\CertificationMail;
use Certification\Form\CertificationMailForm;
use Zend\Mail;
   use Zend\Mail\Message;
   use Zend\Mail\Transport\Sendmail;


class CertificationMailController extends AbstractActionController {

    protected $mailTable;

    public function getCertificationMailTable() {
        if (!$this->mailTable) {
            $sm = $this->getServiceLocator();
            $this->mailTable = $sm->get('Certification\Model\CertificationMailTable');
        }
        return $this->mailTable;
    }

    public function indexAction() {

        $form = new CertificationMailForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $mail = new CertificationMail();
            $form->setInputFilter($mail->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $mail->exchangeArray($form->getData());
                $this->getCertificationMailTable()->saveCertificationMail($mail);

                // Redirect to list of albums
                return $this->redirect()->toRoute('certification-mail');
            }
        }
        return array('form' => $form);
    }

    public function sendmailAction() {
//        
         $form = new CertificationMailForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();
        if ($request->isPost()) {
     $to      = 'adjaratoudabre22@gmail.com';
     $subject = 'the subject';
     $message = 'hello';
     $headers = 'From: adjaratoudabre22@gmail.com' . "\r\n" .
    'Reply-To: webmaster@example.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
    
    return $this->redirect()->toRoute('certification-mail');
        }
        return array('form' => $form);

    }

}
