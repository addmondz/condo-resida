import client from "./client";

export default {
    async getCsrfCookie() {
        return axios.get("/sanctum/csrf-cookie");
    },

    async register(data) {
        await this.getCsrfCookie();
        return client.post("/auth/register", data);
    },

    async login(credentials) {
        await this.getCsrfCookie();
        return client.post("/auth/login", credentials);
    },

    async logout() {
        return client.post("/auth/logout");
    },

    async me() {
        return client.get("/auth/me");
    },

    async forgotPassword(data) {
        await this.getCsrfCookie();
        return client.post("/auth/forgot-password", data);
    },

    async resetPassword(data) {
        await this.getCsrfCookie();
        return client.post("/auth/reset-password", data);
    },

    async changePassword(data) {
        return client.put("/auth/change-password", data);
    },

    async completeOnboarding(data) {
        return client.post("/auth/onboarding", data);
    },

    async getProperties() {
        return client.get("/auth/properties");
    },

    async getBlocks(propertyUuid) {
        return client.get(`/auth/properties/${propertyUuid}/blocks`);
    },

    async getUnits(blockUuid) {
        return client.get(`/auth/blocks/${blockUuid}/units`);
    },
};
