import axios from "axios";
import API_BASE_URL from "./config.js";
import { getToken, clearToken } from "./utils/auth.js";

const api = axios.create({
  baseURL: API_BASE_URL,
});

api.interceptors.request.use(
  (config) => {
    const token = getToken();
    if (token) {
      config.headers = config.headers || {};
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      clearToken();
      if (typeof window !== "undefined") {
        window.dispatchEvent(new Event("logout"));
      }
    }
    return Promise.reject(error);
  }
);

export default api;
