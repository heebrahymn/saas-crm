import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './Contexts/AuthContext';
import { TenantProvider } from './Contexts/TenantContext';
import Login from './Components/Auth/Login';
import Register from './Components/Auth/Register';
import Dashboard from './Components/Dashboard/Dashboard';
import Layout from './Components/Layout/Layout';
import ProtectedRoute from './Components/Auth/ProtectedRoute';
import TenantRoute from './Components/Auth/TenantRoute';
import Contacts from './Components/CRM/Contacts';
import Leads from './Components/CRM/Leads';
import Deals from './Components/CRM/Deals';
import Tasks from './Components/CRM/Tasks';
import Profile from './Components/User/Profile';
import Billing from './Components/Billing/Billing';
import Team from './Components/User/Team';
import InvitationAccept from './Components/Auth/InvitationAccept';

function AppContent() {
    const { loading } = useAuth();

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <Router>
            <Routes>
                {/* Public routes */}
                <Route path="/login" element={<Login />} />
                <Route path="/register" element={<Register />} />
                <Route path="/accept-invitation/:token" element={<InvitationAccept />} />
                
                {/* Tenant routes (require subdomain) */}
                <Route 
                    path="/*" 
                    element={
                        <TenantRoute>
                            <ProtectedRoute>
                                <Layout>
                                    <Routes>
                                        <Route path="/" element={<Dashboard />} />
                                        <Route path="/dashboard" element={<Dashboard />} />
                                        <Route path="/contacts" element={<Contacts />} />
                                        <Route path="/leads" element={<Leads />} />
                                        <Route path="/deals" element={<Deals />} />
                                        <Route path="/tasks" element={<Tasks />} />
                                        <Route path="/profile" element={<Profile />} />
                                        <Route path="/billing" element={<Billing />} />
                                        <Route path="/team" element={<Team />} />
                                    </Routes>
                                </Layout>
                            </ProtectedRoute>
                        </TenantRoute>
                    } 
                />
                
                {/* Redirect unknown routes */}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </Router>
    );
}

function App() {
    return (
        <AuthProvider>
            <TenantProvider>
                <AppContent />
            </TenantProvider>
        </AuthProvider>
    );
    
}

export default App;