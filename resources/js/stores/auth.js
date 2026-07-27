import { defineStore } from "pinia";
import { ref, computed } from "vue";
import authApi from "@/api/auth";

export const useAuthStore = defineStore("auth", () => {
    const user = ref(null);
    const token = ref(localStorage.getItem("auth_token"));
    const loading = ref(false);
    const initialized = ref(false);

    const isAuthenticated = computed(() => !!user.value && !!token.value);
    const isApproved = computed(() => user.value?.status === "approved");
    const isPending = computed(() => user.value?.status === "pending");
    const isRejected = computed(() => user.value?.status === "rejected");
    const isSuspended = computed(() => user.value?.status === "suspended");
    const needsOnboarding = computed(() => {
        if (!user.value) return false;
        const role =
            typeof user.value.roles?.[0] === "string"
                ? user.value.roles[0]
                : user.value.roles?.[0]?.name;
        if (role && role !== "resident") return false;
        return !user.value.has_unit_assignment;
    });
    const userRole = computed(() => {
        const role = user.value?.roles?.[0];

        if (typeof role === "string") {
            return role;
        }

        return role?.name || null;
    });
    const isAdmin = computed(() =>
        ["super_admin", "property_admin"].includes(userRole.value),
    );
    const isSuperAdmin = computed(() => userRole.value === "super_admin");
    const isGuard = computed(() => userRole.value === "guard");
    const isResident = computed(() => userRole.value === "resident");

    function setAuth(userData, authToken) {
        user.value = userData;
        token.value = authToken;
        if (authToken) {
            localStorage.setItem("auth_token", authToken);
        }
    }

    function clearAuth() {
        user.value = null;
        token.value = null;
        localStorage.removeItem("auth_token");
    }

    async function fetchUser() {
        if (!token.value) {
            initialized.value = true;
            return;
        }
        try {
            loading.value = true;
            const { data } = await authApi.me();
            user.value = data.data;
        } catch {
            clearAuth();
        } finally {
            loading.value = false;
            initialized.value = true;
        }
    }

    async function login(credentials) {
        const { data } = await authApi.login(credentials);
        setAuth(data.data.user, data.data.token);
        return data;
    }

    async function register(formData) {
        const { data } = await authApi.register(formData);
        setAuth(data.data.user, data.data.token);
        return data;
    }

    async function logout() {
        try {
            await authApi.logout();
        } finally {
            clearAuth();
        }
    }

    async function forgotPassword(formData) {
        return authApi.forgotPassword(formData);
    }

    async function resetPassword(formData) {
        return authApi.resetPassword(formData);
    }

    async function changePassword(formData) {
        return authApi.changePassword(formData);
    }

    function getHomePath() {
        if (isAdmin.value) return "/admin/dashboard";
        if (isGuard.value) return "/guard/dashboard";
        if (needsOnboarding.value) return "/onboarding";
        if (!isApproved.value) {
            if (isPending.value) return "/pending-approval";
            if (isRejected.value) return "/rejected";
            return "/login";
        }
        return "/resident/dashboard";
    }

    return {
        user,
        token,
        loading,
        initialized,
        isAuthenticated,
        isApproved,
        isPending,
        isRejected,
        isSuspended,
        needsOnboarding,
        userRole,
        isAdmin,
        isSuperAdmin,
        isGuard,
        isResident,
        setAuth,
        clearAuth,
        fetchUser,
        login,
        register,
        logout,
        forgotPassword,
        resetPassword,
        changePassword,
        getHomePath,
    };
});
