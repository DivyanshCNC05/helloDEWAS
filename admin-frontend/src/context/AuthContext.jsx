import React, { createContext, useContext, useEffect, useState } from "react";
import api from "../apiClient.js";
import { getToken, setToken, clearToken } from "../utils/auth.js";

const AuthContext = createContext({
  isAuthenticated: false,
  loading: true,
  login: async () => {},
  logout: () => {},
  validateToken: async () => false,
});

export function AuthProvider({ children }) {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);

  const validateToken = async () => {
    const token = getToken();
    if (!token) {
      setIsAuthenticated(false);
      setLoading(false);
      return false;
    }

    try {
      const response = await api.get("/api/auth/validate");
      if (response.data?.valid) {
        setIsAuthenticated(true);
        return true;
      }
    } catch (error) {
      console.error("Auth validation failed:", error.response?.data || error.message);
      clearToken();
      setIsAuthenticated(false);
      return false;
    } finally {
      setLoading(false);
    }

    return false;
  };

  useEffect(() => {
    validateToken();

    const handleLogoutEvent = () => {
      clearToken();
      setIsAuthenticated(false);
    };

    window.addEventListener("logout", handleLogoutEvent);
    return () => window.removeEventListener("logout", handleLogoutEvent);
  }, []);

  const login = async (token) => {
    setToken(token);
    setIsAuthenticated(true);
  };

  const logout = () => {
    clearToken();
    setIsAuthenticated(false);
  };

  return (
    <AuthContext.Provider value={{ isAuthenticated, loading, login, logout, validateToken }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
