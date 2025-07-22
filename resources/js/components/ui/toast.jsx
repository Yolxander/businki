import React, { createContext, useContext, useState } from 'react';
import { X, CheckCircle, AlertCircle, Info } from 'lucide-react';

const ToastContext = createContext();

export const useToast = () => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};

export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const addToast = (message, type = 'success') => {
        const id = Date.now();
        const newToast = { id, message, type };
        setToasts(prev => [...prev, newToast]);

        // Auto remove after 3 seconds
        setTimeout(() => {
            removeToast(id);
        }, 3000);
    };

    const removeToast = (id) => {
        setToasts(prev => prev.filter(toast => toast.id !== id));
    };

    const success = (message) => addToast(message, 'success');
    const error = (message) => addToast(message, 'error');
    const info = (message) => addToast(message, 'info');

    return (
        <ToastContext.Provider value={{ success, error, info, removeToast }}>
            {children}
            <div className="fixed top-4 right-4 z-50 space-y-2">
                {toasts.map((toast) => (
                    <Toast key={toast.id} toast={toast} onRemove={removeToast} />
                ))}
            </div>
        </ToastContext.Provider>
    );
};

const Toast = ({ toast, onRemove }) => {
    const getIcon = () => {
        switch (toast.type) {
            case 'success':
                return <CheckCircle className="w-5 h-5 text-green-500" />;
            case 'error':
                return <AlertCircle className="w-5 h-5 text-red-500" />;
            case 'info':
                return <Info className="w-5 h-5 text-blue-500" />;
            default:
                return <CheckCircle className="w-5 h-5 text-green-500" />;
        }
    };

    const getBgColor = () => {
        switch (toast.type) {
            case 'success':
                return 'bg-green-50 border-green-200';
            case 'error':
                return 'bg-red-50 border-red-200';
            case 'info':
                return 'bg-blue-50 border-blue-200';
            default:
                return 'bg-green-50 border-green-200';
        }
    };

    return (
        <div className={`${getBgColor()} border rounded-lg shadow-lg p-4 max-w-sm transition-all duration-300 ease-in-out transform translate-x-0 opacity-100`}>
            <div className="flex items-start space-x-3">
                {getIcon()}
                <div className="flex-1">
                    <p className="text-sm font-medium text-gray-900">{toast.message}</p>
                </div>
                <button
                    onClick={() => onRemove(toast.id)}
                    className="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <X className="w-4 h-4" />
                </button>
            </div>
        </div>
    );
};
