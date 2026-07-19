import client from "./client";

export default {
    getDashboard() {
        return client.get("/guard/dashboard");
    },

    validateQr(data) {
        return client.post("/guard/qr/validate", data);
    },

    checkIn(uuid, data = {}) {
        return client.post(`/guard/visitors/${uuid}/check-in`, data);
    },

    checkOut(uuid, data = {}) {
        return client.post(`/guard/visitors/${uuid}/check-out`, data);
    },

    getVisitors(params = {}) {
        return client.get("/guard/visitors", { params });
    },

    getVisitor(uuid) {
        return client.get(`/guard/visitors/${uuid}`);
    },

    getActivityLogs(params = {}) {
        return client.get("/guard/activity-logs", { params });
    },
};
