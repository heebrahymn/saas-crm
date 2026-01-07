import React, { useState, useEffect } from 'react';
import { useAuth } from '../../Contexts/AuthContext';
import { useTenant } from '../../Contexts/TenantContext';
import axios from 'axios';

interface Contact {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    created_at: string;
}

interface Task {
    id: number;
    title: string;
    due_date: string | null;
    status: string;
    assignedUser?: {
        name: string;
    };
}

interface DashboardData {
    stats: {
        contacts: number;
        leads: number;
        deals: number;
        tasks: number;
    };
    recent: {
        contacts: Contact[];
        leads: any[];
        deals: any[];
        tasks: any[];
    };
    upcoming_tasks: Task[];
    pipeline: any[];
    conversion_rate: number;
}

interface StatCardProps {
    title: string;
    value: number;
    icon: React.ComponentType<{ className?: string }>;
    color: string;
}

export default function Dashboard() {
    const { user } = useAuth();
    const { company, subscription } = useTenant();
    const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            const response = await axios.get('/api/dashboard');
            setDashboardData(response.data);
        } catch (error) {
            console.error('Failed to fetch dashboard ', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    const StatCard: React.FC<StatCardProps> = ({ title, value, icon: Icon, color = 'blue' }) => (
        <div className="bg-white overflow-hidden shadow rounded-lg">
            <div className="px-4 py-5 sm:p-6">
                <div className="flex items-center">
                    <div className={`flex-shrink-0 p-3 rounded-md bg-${color}-100`}>
                        <Icon className={`h-6 w-6 text-${color}-600`} />
                    </div>
                    <div className="ml-5 w-0 flex-1">
                        <dl>
                            <dt className="text-sm font-medium text-gray-500 truncate">{title}</dt>
                            <dd className="flex items-baseline">
                                <div className="text-2xl font-semibold text-gray-900">{value}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <div>
            <div className="sm:flex sm:items-center">
                <div className="sm:flex-auto">
                    <h1 className="text-xl font-semibold text-gray-900">Dashboard</h1>
                    <p className="mt-2 text-sm text-gray-700">
                        Welcome back, {user?.name}! Here's what's happening with your CRM.
                    </p>
                </div>
            </div>

            {/* Stats */}
            <div className="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Contacts"
                    value={dashboardData?.stats?.contacts || 0}
                    icon={props => <svg {...props} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>}
                    color="blue"
                />
                <StatCard
                    title="Total Leads"
                    value={dashboardData?.stats?.leads || 0}
                    icon={props => <svg {...props} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>}
                    color="green"
                />
                <StatCard
                    title="Total Deals"
                    value={dashboardData?.stats?.deals || 0}
                    icon={props => <svg {...props} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>}
                    color="purple"
                />
                <StatCard
                    title="Total Tasks"
                    value={dashboardData?.stats?.tasks || 0}
                    icon={props => <svg {...props} fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>}
                    color="yellow"
                />
            </div>

            {/* Recent Activity */}
            <div className="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Recent Contacts */}
                <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 className="text-lg leading-6 font-medium text-gray-900">Recent Contacts</h3>
                    </div>
                    <ul className="divide-y divide-gray-200">
                        {dashboardData?.recent?.contacts?.slice(0, 5).map((contact) => (
                            <li key={contact.id} className="px-4 py-4 sm:px-6">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm font-medium text-blue-600 truncate">
                                        {contact.first_name} {contact.last_name}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        {new Date(contact.created_at).toLocaleDateString()}
                                    </div>
                                </div>
                                <div className="mt-1 text-sm text-gray-500 truncate">
                                    {contact.email}
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Recent Tasks */}
                <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 className="text-lg leading-6 font-medium text-gray-900">Upcoming Tasks</h3>
                    </div>
                    <ul className="divide-y divide-gray-200">
                        {dashboardData?.upcoming_tasks?.slice(0, 5).map((task) => (
                            <li key={task.id} className="px-4 py-4 sm:px-6">
                                <div className="flex items-center justify-between">
                                    <div className="text-sm font-medium text-gray-900 truncate">
                                        {task.title}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        {task.due_date ? new Date(task.due_date).toLocaleDateString() : 'No due date'}
                                    </div>
                                </div>
                                <div className="mt-1 flex items-center justify-between">
                                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                        task.status === 'completed' ? 'bg-green-100 text-green-800' :
                                        task.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-blue-100 text-blue-800'
                                    }`}>
                                        {task.status}
                                    </span>
                                    {task.assignedUser && (
                                        <span className="text-sm text-gray-500">
                                            {task.assignedUser.name}
                                        </span>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>

            {/* Subscription Status */}
            {subscription && (
                <div className="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 className="text-lg leading-6 font-medium text-gray-900">Subscription Status</h3>
                    </div>
                    <div className="px-4 py-5 sm:p-6">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <div className={`h-12 w-12 rounded-full flex items-center justify-center ${
                                    subscription.is_subscribed ? 'bg-green-100' : 'bg-red-100'
                                }`}>
                                    {subscription.is_subscribed ? (
                                        <svg className="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                        </svg>
                                    ) : (
                                        <svg className="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    )}
                                </div>
                            </div>
                            <div className="ml-4">
                                <h4 className="text-sm font-medium text-gray-900">
                                    {subscription.is_subscribed ? 'Active Subscription' : 'No Active Subscription'}
                                </h4>
                                <p className="text-sm text-gray-500">
                                    {subscription.plan?.name || 'Free Trial'} - 
                                    {subscription.is_subscribed ? ' Active' : 
                                     subscription.is_on_trial ? ' Trial ends soon' : ' Expired'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}