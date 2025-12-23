import axios from 'axios';
window.axios = axios;

window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://localhost:8000';
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// ✅ Add CSRF Token to every request header
window.axios.interceptors.request.use((config) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content ||
                getCsrfTokenFromCookie();
  
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }
  
  return config;
});

function getCsrfTokenFromCookie() {
  const cookies = document.cookie.split(';');
  for (let cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value);
    }
  }
  return null;
}

