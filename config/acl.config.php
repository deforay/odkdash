<?php
return array(
    'AD' => array(
        'Application\\Controller\\IndexController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'download-pdf' => 'allow',
            'edit' => 'allow',
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\SpiV5Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'download-pdf' => 'allow',
            'edit' => 'allow',
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'allow',
            'change-password' => 'allow',
            'check-password' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\UserLoginHistoryController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\AuditTrailController' => array(
            'index' => 'allow',
        ),
    ),
    'SA' => array(
        'Application\\Controller\\CommonController' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\IndexController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardController' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
        ),
        'Application\\Controller\\DashboardV6Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardV5Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\EmailController' => array(
            'index' => 'allow',
            'email-v5' => 'allow',
            'email-v6' => 'allow',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-province-list' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV5Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'export' => 'allow',
            'view-data-v5' => 'allow',
        ),
        'Application\\Controller\\SpiV6Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'export' => 'allow',
            'view-data-v6' => 'allow',
            'view-data-section-zero-protocol-v6' => 'allow',
            'view-data-section-zero-v6' => 'allow',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'view-data' => 'allow',
            'delete' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'change-password' => 'allow',
            'check-password' => 'allow',
        ),
        'Application\\Controller\\SpiV3ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV5ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV6ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\UserLoginHistoryController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\AuditTrailController' => array(
            'index' => 'allow',
        ),
    ),
    'US' => array(
        'Application\\Controller\\IndexController' => array(
            'index' => 'deny',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'deny',
            'edit-global' => 'deny',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
            'get-facility-name' => 'deny',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'deny',
            'approve-status' => 'deny',
            'download-pdf' => 'allow',
            'edit' => 'deny',
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'deny',
            'add' => 'deny',
            'edit' => 'deny',
            'change-password' => 'allow',
            'check-password' => 'allow',
        ),
    ),
    'VIEWER' => array(
        'Application\\Controller\\CommonController' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\IndexController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardController' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
        ),
        'Application\\Controller\\DashboardV6Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardV5Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\EmailController' => array(
            'index' => 'allow',
            'email-v5' => 'allow',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-province-list' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV5Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'export' => 'allow',
        ),
        'Application\\Controller\\SpiV6Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'export' => 'allow',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'view-data' => 'allow',
            'delete' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'change-password' => 'allow',
            'check-password' => 'allow',
        ),
        'Application\\Controller\\SpiV3ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV5ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV6ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\UserLoginHistoryController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\AuditTrailController' => array(
            'index' => 'allow',
        ),
    ),
    'DE' => array(
        'Application\\Controller\\CommonController' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\IndexController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardController' => array(
            'index' => 'deny',
            'audit-details' => 'deny',
        ),
        'Application\\Controller\\DashboardV6Controller' => array(
            'index' => 'deny',
        ),
        'Application\\Controller\\DashboardV5Controller' => array(
            'index' => 'deny',
        ),
        'Application\\Controller\\EmailController' => array(
            'index' => 'allow',
            'email-v5' => 'deny',
            'email-v6' => 'deny',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-province-list' => 'deny',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV5Controller' => array(
            'index' => 'deny',
            'manage-facility' => 'deny',
            'approve-status' => 'deny',
            'corrective-action-pdf' => 'deny',
            'delete' => 'deny',
            'download-pdf' => 'deny',
            'download-files' => 'deny',
            'duplicate' => 'deny',
            'edit' => 'deny',
            'export' => 'deny',
            'view-data-v5' => 'deny',
        ),
        'Application\\Controller\\SpiV6Controller' => array(
            'index' => 'deny',
            'manage-facility' => 'deny',
            'approve-status' => 'deny',
            'corrective-action-pdf' => 'deny',
            'delete' => 'deny',
            'download-pdf' => 'deny',
            'download-files' => 'deny',
            'duplicate' => 'deny',
            'edit' => 'deny',
            'export' => 'deny',
            'view-data-v6' => 'deny',
            'view-data-section-zero-protocol-v6' => 'deny',
            'view-data-section-zero-v6' => 'deny',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'view-data' => 'deny',
            'delete' => 'allow',
            'download-files' => 'deny',
            'duplicate' => 'deny',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\UserLoginHistoryController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\AuditTrailController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'change-password' => 'allow',
            'check-password' => 'allow',
            'edit' => 'allow',
            'profile' => 'deny',
        ),
        'Application\\Controller\\SpiV3ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV5ReportsController' => array(
            'facility-report' => 'deny',
        ),
        'Application\\Controller\\SpiV6ReportsController' => array(
            'facility-report' => 'deny',
        ),
    ),
    'Admin' => array(
        'Application\\Controller\\CommonController' => array(
            'audit-locations' => 'allow',
        ),
        'Application\\Controller\\ConfigController' => array(
            'index' => 'allow',
            'edit-global' => 'allow',
        ),
        'Application\\Controller\\IndexController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardController' => array(
            'index' => 'allow',
            'audit-details' => 'allow',
        ),
        'Application\\Controller\\DashboardV6Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\DashboardV5Controller' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\EmailController' => array(
            'index' => 'allow',
            'email-v5' => 'allow',
            'email-v6' => 'allow',
        ),
        'Application\\Controller\\FacilityController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
            'get-province-list' => 'allow',
            'get-facility-name' => 'allow',
        ),
        'Application\\Controller\\RolesController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV5Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'export' => 'allow',
            'view-data-v5' => 'allow',
        ),
        'Application\\Controller\\SpiV6Controller' => array(
            'index' => 'allow',
            'manage-facility' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'delete' => 'allow',
            'download-pdf' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'email-v6' => 'allow',
            'export' => 'allow',
            'view-data-v6' => 'allow',
            'view-data-section-zero-protocol-v6' => 'allow',
            'view-data-section-zero-v6' => 'allow',
        ),
        'Application\\Controller\\SpiV3Controller' => array(
            'index' => 'allow',
            'approve-status' => 'allow',
            'corrective-action-pdf' => 'allow',
            'view-data' => 'allow',
            'delete' => 'allow',
            'download-files' => 'allow',
            'duplicate' => 'allow',
            'edit' => 'allow',
            'manage-facility' => 'allow',
            'download-pdf' => 'allow',
        ),
        'Application\\Controller\\UserLoginHistoryController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\EventController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\AuditTrailController' => array(
            'index' => 'allow',
        ),
        'Application\\Controller\\UsersController' => array(
            'index' => 'allow',
            'add' => 'allow',
            'change-password' => 'allow',
            'check-password' => 'allow',
            'edit' => 'allow',
        ),
        'Application\\Controller\\SpiV3ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV5ReportsController' => array(
            'facility-report' => 'allow',
        ),
        'Application\\Controller\\SpiV6ReportsController' => array(
            'facility-report' => 'allow',
        ),
    ),
);
