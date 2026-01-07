import React, { useState } from 'react';
import { useAuth } from '../../Contexts/AuthContext';
import { Link } from 'react-router-dom';

interface FormData {
    email: string;
    password: string;
}

interface Errors {
    [key: string]: string[];
}

export default function Login() {
    const [formData, setFormData] = useState<FormData>({
        email: '',
        password: ''
    });
    const [errors, setErrors] = useState<Errors>({});
    const [loading, setLoading] = useState(false);
    
    const { login } = useAuth();

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
        // Clear error when user types
        if (errors[e.target.name]) {
            setErrors({
                ...errors,
                [e.target.name]: []
            });
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        try {
            await login(formData.email, formData.password);
        } catch (error: any) {
            if (error.errors) {
                setErrors(error.errors);
            } else {
                setErrors({ general: [error.message || 'Login failed'] });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8">
                <div>
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Sign in to your account
                    </h2>
                </div>
                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                    {errors.general && (
                        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            {errors.general[0]}
                        </div>
                    )}
                    
                    <div className="rounded-md shadow-sm -space-y-px">
                        <div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                className={`appearance-none rounded-none relative block w-full px-3 py-2 border ${
                                    errors.email ? 'border-red-300' : 'border-gray-300'
                                } placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm`}
                                placeholder="Email address"
                                value={formData.email}
                                onChange={handleChange}
                            />
                            {errors.email && (
                                <p className="mt-1 text-sm text-red-600">{errors.email[0]}</p>
                            )}
                        </div>
                        <div>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                className={`appearance-none rounded-none relative block w-full px-3 py-2 border ${
                                    errors.password ? 'border-red-300' : 'border-gray-300'
                                } placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm`}
                                placeholder="Password"
                                value={formData.password}
                                onChange={handleChange}
                            />
                            {errors.password && (
                                <p className="mt-1 text-sm text-red-600">{errors.password[0]}</p>
                            )}
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            disabled={loading}
                            className="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {loading ? (
                                <span className="flex items-center">
                                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Signing in...
                                </span>
                            ) : (
                                'Sign in'
                            )}
                        </button>
                    </div>
                    
                    <div className="text-center">
                        <Link to="/register" className="text-blue-600 hover:text-blue-500">
                            Don't have an account? Register
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    );
}