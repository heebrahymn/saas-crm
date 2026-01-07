import React, { useEffect, useState } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../Contexts/AuthContext';

export default function TenantRoute({ children }: { children: React.ReactNode }) {
    const { tenantSubdomain, checkAuth, user } = useAuth();
    const location = useLocation();
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Check if we're on a tenant subdomain
        if (tenantSubdomain) {
            // Initialize auth for tenant
            checkAuth().finally(() => {
                setLoading(false);
            });
        } else {
            // If no tenant subdomain, redirect to login or main domain
            setLoading(false);
        }
    }, [tenantSubdomain, checkAuth]);

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    // If no tenant subdomain, redirect to main domain login
    if (!tenantSubdomain) {
        const mainDomain = window.location.hostname.replace(/^[^.]+\./, '');
        window.location.href = `http://app.${mainDomain}`;
        return null;
    }

    // If we have a tenant subdomain but no auth token, redirect to login
    if (tenantSubdomain && !user) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    return children;
}