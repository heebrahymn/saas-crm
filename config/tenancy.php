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
            'contacts' => env('DATA_RETENTION_CONTACTS_DAYS', 365), // days
            'leads' => env('DATA_RETENTION_LEADS_DAYS', 365),
            'deals' => env('DATA_RETENTION_DEALS_DAYS', 365),
            'tasks' => env('DATA_RETENTION_TASKS_DAYS', 180),
        ],
    ],
    
    'gdpr' => [
        'enabled' => true,
        'data_export_enabled' => true,
        'data_deletion_enabled' => true,
        'consent_tracking' => true,
        'consent_purposes' => [
            'marketing',
            'analytics',
            'essential',
        ],
    ],
];