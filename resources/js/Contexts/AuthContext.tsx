import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    company_id: number;
}

interface AuthContextType {
    user: User | null;
    login: (email: string, password: string) => Promise<any>;
    register: (companyName: string, subdomain: string, email: string, password: string) => Promise<any>;
    logout: () => Promise<void>;
    checkAuth: () => Promise<void>;
    loading: boolean;
    tenantSubdomain: string | null;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);
    const [tenantSubdomain, setTenantSubdomain] = useState<string | null>(null);

    useEffect(() => {
        // Check if we're on a tenant subdomain
        const hostname = window.location.hostname;
        const tenantMatch = hostname.match(/^([^.]+)\.app\.test$/);
        if (tenantMatch) {
            setTenantSubdomain(tenantMatch[1]);
        }
        
        // Check auth status on mount
        checkAuth();
    }, []);

    const login = async (email: string, password: string) => {
        try {
            const response = await axios.post('/api/login', {
                email,
                password
            });

            const { user, token, redirect_url } = response.data;

            // Set auth token
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            localStorage.setItem('auth_token', token);

            setUser(user);

            // Redirect to tenant subdomain if needed
            if (redirect_url && window.location.origin !== redirect_url) {
                window.location.href = redirect_url;
                return;
            }

            return response.data;
        } catch (error: any) {
            throw error.response?.data || { message: 'Login failed' };
        }
    };

    const register = async (companyName: string, subdomain: string, email: string, password: string) => {
        try {
            const response = await axios.post('/api/register', {
                company_name: companyName,
                subdomain,
                email,
                password,
                password_confirmation: password
            });

            const { user, token, redirect_url } = response.data;

            // Set auth token
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            localStorage.setItem('auth_token', token);

            setUser(user);

            // Redirect to new tenant subdomain
            if (redirect_url) {
                window.location.href = redirect_url;
            }

            return response.data;
        } catch (error: any) {
            throw error.response?.data || { message: 'Registration failed' };
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/logout');
        } catch (error) {
            // Continue with logout even if API call fails
        } finally {
            // Clear auth token
            delete axios.defaults.headers.common['Authorization'];
            localStorage.removeItem('auth_token');
            setUser(null);
        }
    };

    const checkAuth = async () => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            try {
                const response = await axios.get('/api/me');
                setUser(response.data.user);
            } catch (error) {
                // Token is invalid, clear it
                delete axios.defaults.headers.common['Authorization'];
                localStorage.removeItem('auth_token');
                setUser(null);
            }
        }
        setLoading(false);
    };

    const value = {
        user,
        login,
        register,
        logout,
        checkAuth,
        loading,
        tenantSubdomain
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};