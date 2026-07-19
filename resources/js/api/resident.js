import client from "./client";

export default {
    getDashboard() {
        return client.get("/resident/dashboard");
    },

    getProfile() {
        return client.get("/resident/profile");
    },

    updateProfile(data) {
        return client.put("/resident/profile", data);
    },

    getVisitors(params = {}) {
        return client.get("/resident/visitors", { params });
    },

    createVisitor(data) {
        return client.post("/resident/visitors", data);
    },

    getVisitor(uuid) {
        return client.get(`/resident/visitors/${uuid}`);
    },

    cancelVisitor(uuid, data = {}) {
        return client.post(`/resident/visitors/${uuid}/cancel`, data);
    },

    getVisitorQr(uuid) {
        return client.get(`/resident/visitors/${uuid}/qr`);
    },

    getFacilities(params = {}) {
        return client.get("/resident/facilities", { params });
    },

    getFacility(uuid) {
        return client.get(`/resident/facilities/${uuid}`);
    },

    getFacilityAvailability(uuid, params = {}) {
        return client.get(`/resident/facilities/${uuid}/availability`, {
            params,
        });
    },

    getBookings(params = {}) {
        return client.get("/resident/bookings", { params });
    },

    getBooking(uuid) {
        return client.get(`/resident/bookings/${uuid}`);
    },

    createBooking(data) {
        return client.post("/resident/bookings", data);
    },

    cancelBooking(uuid, data = {}) {
        return client.post(`/resident/bookings/${uuid}/cancel`, data);
    },

    getNotifications(params = {}) {
        return client.get("/resident/notifications", { params });
    },

    markNotificationRead(id) {
        return client.post(`/resident/notifications/${id}/read`);
    },

    markAllNotificationsRead() {
        return client.post("/resident/notifications/read-all");
    },
};
