export const TOKEN_KEY = "token";

export const getToken = () => {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem(TOKEN_KEY);
};

export const setToken = (token) => {
  if (typeof window === "undefined") return;
  if (token) {
    window.localStorage.setItem(TOKEN_KEY, token);
  } else {
    window.localStorage.removeItem(TOKEN_KEY);
  }
};

export const clearToken = () => {
  if (typeof window === "undefined") return;
  window.localStorage.removeItem(TOKEN_KEY);
};

export const getAuthHeader = () => {
  const token = getToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
};

export default {
  TOKEN_KEY,
  getToken,
  setToken,
  clearToken,
  getAuthHeader,
};
