<?php
return array(
    'AD' => array(
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\Roles' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'download-pdf' => 'allow',
            'edit' => 'allow',
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
    ),
    'SA' => array(
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audit-details' => 'deny',
        ),
        'Application\\Controller\\Email' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-province-list' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\Roles' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3Reports' => array(
            'facility-report' => 'allow',
        ),
//        acl for SA
        
         'Certification\\Controller\\Certification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'pdf' => 'allow',
             'search' => 'allow',
        ),
        'Certification\\Controller\\CertificationIssuer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\ExamAdmin' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        
        'Certification\\Controller\\Provider' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'certification' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\PracticalExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Recertification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Trainer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Training' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\TrainingOrganization' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\WrittenExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\CertificationMail' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'sendmail'=>'allow'
        ),
        'Certification\\Controller\\Examination'=> array(
            'index' => 'allow',
            'add' => 'allow',
            'search' => 'allow',
        ),
        
    ),
    'US' => array(
        'Application\\Controller\\Index' => array(
            'index' => 'deny',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'deny',
            'edit-global' => 'deny',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
            'get-facility-name' => 'deny',
        ),
        'Application\\Controller\\Roles' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'manage-facility' => 'deny',
            'approve-status' => 'deny',
            'download-pdf' => 'allow',
            'edit' => 'deny',
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
        ),
    ),
    'VIEWER' => array(
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'deny',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'allow',
            'edit-global' => 'deny',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audi-details' => 'deny',
        ),
        'Application\\Controller\\Email' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'allow',
            'add' => 'deny',
            'edit' => 'deny',
            'get-facility-name' => 'deny',
        ),
        'Application\\Controller\\Roles' => array(
            'index' => 'allow',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'approve-status' => 'deny',
            'corrective-action-pdf' => 'deny',
            'delete' => 'deny',
            'edit' => 'deny',
            'manage-facility' => 'deny',
            'download-pdf' => 'deny',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'allow',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3Reports' => array(
            'facility-report' => 'allow',
        ),
    ),
    'DE' => array(
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Email' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\Roles' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3Reports' => array(
            'facility-report' => 'allow',
        ),
    ),
    
//   acl for personnel certitication viewer PCV
    'PCV' => array(
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
        ),
        
         'Certification\\Controller\\Certification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'pdf' => 'allow',
             'search' => 'allow',
        ),
        'Certification\\Controller\\CertificationIssuer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\ExamAdmin' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        
        'Certification\\Controller\\Provider' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'certification' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\PracticalExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Recertification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Trainer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\Training' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\TrainingOrganization' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\WrittenExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'search' => 'allow',
        ),
        'Certification\\Controller\\CertificationMail' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'sendmail'=>'allow'
        ),
        'Certification\\Controller\\Examination'=> array(
            'index' => 'allow',
            'add' => 'allow',
            'search' => 'allow',
        ),
    ),
);