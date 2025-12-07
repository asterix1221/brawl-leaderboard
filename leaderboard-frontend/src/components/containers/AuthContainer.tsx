import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { useAuthStore } from '../../store/slices/authSlice';
import Input from '../presentational/Common/Input';
import Button from '../presentational/Common/Button';

interface AuthContainerProps {
  mode: 'login' | 'register';
  onToggleMode: () => void;
}

const AuthContainer: React.FC<AuthContainerProps> = ({ mode, onToggleMode }) => {
  const navigate = useNavigate();
  const { login, register, loading, error } = useAuth();
  const { isAuthenticated } = useAuthStore();
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    nickname: '',
  });
  
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  // Redirect if already authenticated
  React.useEffect(() => {
    if (isAuthenticated) {
      navigate('/');
    }
  }, [isAuthenticated, navigate]);

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    
    // Clear validation error for this field
    if (validationErrors[field]) {
      setValidationErrors(prev => ({ ...prev, [field]: '' }));
    }
  };

  const validateForm = (): boolean => {
    const errors: Record<string, string> = {};
    
    // Email validation
    if (!formData.email.trim()) {
      errors.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      errors.email = 'Please enter a valid email';
    }
    
    // Password validation
    if (!formData.password) {
      errors.password = 'Password is required';
    } else if (formData.password.length < 8) {
      errors.password = 'Password must be at least 8 characters';
    }
    
    // Nickname validation (only for register)
    if (mode === 'register') {
      if (!formData.nickname.trim()) {
        errors.nickname = 'Nickname is required';
      } else if (formData.nickname.length < 3) {
        errors.nickname = 'Nickname must be at least 3 characters';
      } else if (formData.nickname.length > 30) {
        errors.nickname = 'Nickname must be less than 30 characters';
      } else if (!/^[a-zA-Z0-9_-]+$/.test(formData.nickname)) {
        errors.nickname = 'Nickname can only contain letters, numbers, underscores, and hyphens';
      }
    }
    
    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }
    
    try {
      if (mode === 'login') {
        await login({ 
          email: formData.email, 
          password: formData.password 
        });
      } else {
        await register({ 
          email: formData.email, 
          password: formData.password, 
          nickname: formData.nickname 
        });
      }
    } catch (err) {
      // Error is handled by the hook
    }
  };

  const title = mode === 'login' ? 'Sign In' : 'Create Account';
  const submitText = mode === 'login' ? 'Sign In' : 'Create Account';
  const toggleText = mode === 'login' 
    ? "Don't have an account? Sign up" 
    : "Already have an account? Sign in";

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md w-full space-y-8">
        <div>
          <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
            {title}
          </h2>
          <p className="mt-2 text-center text-sm text-gray-600">
            Brawl Stars Leaderboard
          </p>
        </div>
        
        <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
          <div className="space-y-4">
            <Input
              type="email"
              label="Email"
              value={formData.email}
              onChange={(e) => handleInputChange('email', e.target.value)}
              error={validationErrors.email}
              required
              autoComplete="email"
            />
            
            <Input
              type="password"
              label="Password"
              value={formData.password}
              onChange={(e) => handleInputChange('password', e.target.value)}
              error={validationErrors.password}
              required
              autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
            />
            
            {mode === 'register' && (
              <Input
                type="text"
                label="Nickname"
                value={formData.nickname}
                onChange={(e) => handleInputChange('nickname', e.target.value)}
                error={validationErrors.nickname}
                required
                autoComplete="username"
                helperText="3-30 characters, letters, numbers, underscores, and hyphens only"
              />
            )}
          </div>

          {error && (
            <div className="rounded-md bg-red-50 p-4">
              <div className="flex">
                <svg className="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
                <div className="ml-3">
                  <p className="text-sm text-red-800">{error}</p>
                </div>
              </div>
            </div>
          )}

          <div>
            <Button
              type="submit"
              loading={loading}
              disabled={loading}
              className="w-full"
              size="lg"
            >
              {submitText}
            </Button>
          </div>

          <div className="text-center">
            <button
              type="button"
              onClick={onToggleMode}
              className="text-sm text-blue-600 hover:text-blue-500"
            >
              {toggleText}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AuthContainer;