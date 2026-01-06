<?php

return [
    'subdomain_suffix' => env('TENANT_SUBDOMAIN_SUFFIX', '.saas-crm.test'),
    
    'caching' => [
        'tenant_resolution' => 300, // 5 minutes
        'subscription_status' => 60, // 1 minute
        'user_permissions' => 900, // 15 minutes
    ],
    
    'data_retention' => [
        'enabled' => true,
        'policies' => [
            'contacts' => 365, // days
            'leads' => 365,
            'deals' => 365,
            'tasks' => 180,
        ],
    ],
    
    'gdpr' => [
        'enabled' => true,
        'data_export_enabled' => true,
        'data_deletion_enabled' => true,
        'consent_tracking' => true,
    ],
];