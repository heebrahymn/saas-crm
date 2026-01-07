import React, { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../Contexts/AuthContext';
import { useTenant } from '../../Contexts/TenantContext';
import { 
    HomeIcon, 
    UserGroupIcon, 
    DocumentTextIcon, 
    CurrencyDollarIcon, 
    CheckCircleIcon,
    CogIcon,
    CreditCardIcon,
    UserCircleIcon,
    LogoutIcon
} from '@heroicons/react/outline';

interface NavigationItem {
    name: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
}

interface UserNavigationItem {
    name: string;
    href?: string;
    onClick?: () => void;
}

export default function Layout({ children }: { children: React.ReactNode }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { user, logout } = useAuth();
    const { company, subscription } = useTenant();
    const location = useLocation();
    const navigate = useNavigate();

    const navigation: NavigationItem[] = [
        { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
        { name: 'Contacts', href: '/contacts', icon: UserGroupIcon },
        { name: 'Leads', href: '/leads', icon: DocumentTextIcon },
        { name: 'Deals', href: '/deals', icon: CurrencyDollarIcon },
        { name: 'Tasks', href: '/tasks', icon: CheckCircleIcon },
    ];

    const userNavigation: UserNavigationItem[] = [
        { name: 'Your Profile', href: '/profile' },
        { name: 'Billing', href: '/billing' },
        { name: 'Team', href: '/team' },
        { name: 'Sign out', onClick: () => handleLogout() },
    ];

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    const isActive = (path: string) => location.pathname === path;

    if (!user) {
        return <div>{children}</div>;
    }

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Mobile sidebar */}
            <div className="md:hidden">
                <div className="fixed inset-0 z-40 flex">
                    {sidebarOpen ? (
                        <div
                            className="fixed inset-0 z-40 transition-opacity bg-gray-600 bg-opacity-75"
                            onClick={() => setSidebarOpen(false)}
                        />
                    ) : null}
                    
                    <div
                        className={`relative flex-1 flex flex-col max-w-xs w-full bg-white transform ${
                            sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                        } transition-transform duration-300 ease-in-out`}
                    >
                        <div className="absolute top-0 right-0 -mr-12 pt-2">
                            <button
                                type="button"
                                className="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                                onClick={() => setSidebarOpen(false)}
                            >
                                <span className="sr-only">Close sidebar</span>
                                <svg
                                    className="h-6 w-6 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div className="flex-1 h-0 pt-5 pb-4 overflow-y-auto">
                            <div className="flex-shrink-0 flex items-center px-4">
                                <h1 className="text-xl font-bold text-gray-800">{company?.name || 'CRM'}</h1>
                            </div>
                            <nav className="mt-5 px-2 space-y-1">
                                {navigation.map((item) => (
                                    <Link
                                        key={item.name}
                                        to={item.href}
                                        className={`${
                                            isActive(item.href)
                                                ? 'bg-gray-100 text-gray-900'
                                                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                        } group flex items-center px-2 py-2 text-base font-medium rounded-md`}
                                    >
                                        <item.icon
                                            className={`${
                                                isActive(item.href) ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500'
                                            } mr-4 flex-shrink-0 h-6 w-6`}
                                            aria-hidden="true"
                                        />
                                        {item.name}
                                    </Link>
                                ))}
                            </nav>
                        </div>
                        <div className="flex-shrink-0 flex border-t border-gray-200 p-4">
                            <button
                                onClick={handleLogout}
                                className="flex-shrink-0 w-full group block"
                            >
                                <div className="flex items-center">
                                    <div>
                                        <UserCircleIcon className="inline-block h-9 w-9 rounded-full" />
                                    </div>
                                    <div className="ml-3">
                                        <p className="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                            {user.name}
                                        </p>
                                        <p className="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                            {user.email}
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Desktop sidebar */}
            <div className="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
                <div className="flex-1 flex flex-col min-h-0 border-r border-gray-200 bg-white">
                    <div className="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                        <div className="flex items-center flex-shrink-0 px-4">
                            <h1 className="text-xl font-bold text-gray-800">{company?.name || 'CRM'}</h1>
                        </div>
                        <nav className="mt-5 flex-1 px-2 bg-white space-y-1">
                            {navigation.map((item) => (
                                <Link
                                    key={item.name}
                                    to={item.href}
                                    className={`${
                                        isActive(item.href)
                                            ? 'bg-gray-100 text-gray-900'
                                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                    } group flex items-center px-2 py-2 text-sm font-medium rounded-md`}
                                >
                                    <item.icon
                                        className={`${
                                            isActive(item.href) ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500'
                                        } mr-3 flex-shrink-0 h-6 w-6`}
                                        aria-hidden="true"
                                    />
                                    {item.name}
                                </Link>
                            ))}
                        </nav>
                    </div>
                    <div className="flex-shrink-0 flex border-t border-gray-200 p-4">
                        <button
                            onClick={handleLogout}
                            className="flex-shrink-0 w-full group block"
                        >
                            <div className="flex items-center">
                                <div>
                                    <UserCircleIcon className="inline-block h-9 w-9 rounded-full" />
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                        {user.name}
                                    </p>
                                    <p className="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                        {user.email}
                                    </p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            {/* Main content */}
            <div className="md:pl-64 flex flex-col flex-1">
                <main className="flex-1">
                    <div className="py-6">
                        <div className="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    );
}