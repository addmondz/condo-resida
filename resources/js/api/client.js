import axios from "axios";
import { useAuthStore } from "@/stores/auth";
import router from "@/router";

const client = axios.create({
    baseURL: "/api/v1",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
    withCredentials: true,
    withXSRFToken: true,
});

client.interceptors.request.use((config) => {
    const token = localStorage.getItem("auth_token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

client.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 401) {
            const authStore = useAuthStore();
            authStore.clearAuth();
            router.push({ name: "login" });
        }
        return Promise.reject(error);
    },
);

export default client;
