<?php
return array(
    'AD' => array(
              
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'deny',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        
       'Application\\Controller\\Config' => array(
            'index' => 'allow',
            'edit-global' => 'deny',
        ),
        'Application\\Controller\\Email' => array(
            'index' => 'deny',
        ),
        'Application\\Controller\\Facility' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-facility-name' => 'allow',
        ),
               
        'Application\\Controller\\Roles' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'deny',
            'delete' => 'deny',
            'edit' => 'allow',
            'manage-facility' => 'deny',
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
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
        ),
         ),
       
    
    'SA' => array(
        'Certification\\Controller\\Certification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\CertificationIssuer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Certification\\Controller\\ExamAdmin' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Examination' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
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
        'Certification\\Controller\\Provider' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'certification' => 'allow',
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
        'Certification\\Controller\\PracticalExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Recertification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3Reports' => array(
            'facility-report' => 'allow',
        ),
        'Certification\\Controller\\Trainer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Training' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\TrainingOrganization' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\WrittenExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
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
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audi-details' => 'allow',
        ),
    ),
    'VIEWER' => array(
        'Application\\Controller\\Common' => array(
            'audit-locations' => 'deny',
        ),
        'Application\\Controller\\Config' => array(
            'index' => 'deny',
            'edit-global' => 'deny',
        ),
        'Application\\Controller\\Index' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
        ),
        'Application\\Controller\\Email' => array(
            'index' => 'deny',
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
            'index' => 'deny',
            'approve-status' => 'deny',
            'corrective-action-pdf' => 'deny',
            'delete' => 'deny',
            'edit' => 'deny',
            'manage-facility' => 'deny',
            'download-pdf' => 'deny',
        ),
        'Application\\Controller\\Users' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3Reports' => array(
            'facility-report' => 'deny',
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
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
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
    'PCV' => array(
        'Certification\\Controller\\Certification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'pdf' => 'allow',
        ),
        'Certification\\Controller\\CertificationIssuer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\ExamAdmin' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Examination' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Provider' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'certification' => 'allow',
        ),
        'Certification\\Controller\\PracticalExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Recertification' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Trainer' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\Training' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\TrainingOrganization' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Certification\\Controller\\WrittenExam' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\Dashboard' => array(
            'index' => 'allow',
            'audi-details' => 'allow',
        ),
    ),
);
