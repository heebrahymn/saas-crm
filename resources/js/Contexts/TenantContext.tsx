import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

interface Company {
    id: number;
    name: string;
    subdomain: string;
    email: string;
    settings?: Record<string, any>;
}

interface Subscription {
    is_subscribed: boolean;
    is_on_trial: boolean;
    subscription: any;
    plan: any;
}

interface TenantContextType {
    company: Company | null;
    subscription: Subscription | null;
    fetchCompany: () => Promise<void>;
    fetchSubscription: () => Promise<void>;
    updateCompany: (data: Partial<Company>) => Promise<any>;
    loading: boolean;
}

const TenantContext = createContext<TenantContextType | undefined>(undefined);

export const useTenant = () => {
    const context = useContext(TenantContext);
    if (!context) {
        throw new Error('useTenant must be used within a TenantProvider');
    }
    return context;
};

export const TenantProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [company, setCompany] = useState<Company | null>(null);
    const [subscription, setSubscription] = useState<Subscription | null>(null);
    const [loading, setLoading] = useState(true);

    const fetchCompany = async () => {
        try {
            const response = await axios.get('/api/company');
            setCompany(response.data.company);
        } catch (error) {
            console.error('Failed to fetch company:', error);
        }
    };

    const fetchSubscription = async () => {
        try {
            const response = await axios.get('/api/billing/subscription');
            setSubscription(response.data);
        } catch (error) {
            console.error('Failed to fetch subscription:', error);
        }
    };

    const updateCompany = async (data: Partial<Company>) => {
        try {
            const response = await axios.put('/api/company', data);
            setCompany(response.data.company);
            return response.data;
        } catch (error: any) {
            throw error.response?.data || { message: 'Update failed' };
        }
    };

    useEffect(() => {
        if (axios.defaults.headers.common['Authorization']) {
            Promise.all([
                fetchCompany(),
                fetchSubscription()
            ]).finally(() => {
                setLoading(false);
            });
        } else {
            setLoading(false);
        }
    }, []);

    const value = {
        company,
        subscription,
        fetchCompany,
        fetchSubscription,
        updateCompany,
        loading
    };

    return (
        <TenantContext.Provider value={value}>
            {children}
        </TenantContext.Provider>
    );
};