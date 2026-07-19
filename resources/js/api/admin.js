import client from "./client";

export default {
    getDashboard() {
        return client.get("/admin/dashboard");
    },

    // Users
    getUsers(params = {}) {
        return client.get("/admin/users", { params });
    },

    getUser(uuid) {
        return client.get(`/admin/users/${uuid}`);
    },

    updateUser(uuid, data) {
        return client.put(`/admin/users/${uuid}`, data);
    },

    approveUser(uuid) {
        return client.post(`/admin/users/${uuid}/approve`);
    },

    rejectUser(uuid, data) {
        return client.post(`/admin/users/${uuid}/reject`, data);
    },

    suspendUser(uuid, data = {}) {
        return client.post(`/admin/users/${uuid}/suspend`, data);
    },

    reactivateUser(uuid) {
        return client.post(`/admin/users/${uuid}/reactivate`);
    },

    resetUserPassword(uuid) {
        return client.post(`/admin/users/${uuid}/reset-password`);
    },

    // Visitors
    getVisitors(params = {}) {
        return client.get("/admin/visitors", { params });
    },

    getVisitor(uuid) {
        return client.get(`/admin/visitors/${uuid}`);
    },

    cancelVisitor(uuid, data = {}) {
        return client.post(`/admin/visitors/${uuid}/cancel`, data);
    },

    exportVisitors(params = {}) {
        return client.get("/admin/visitors/export", {
            params,
            responseType: "blob",
        });
    },

    // Facilities
    getFacilities(params = {}) {
        return client.get("/admin/facilities", { params });
    },

    getFacility(uuid) {
        return client.get(`/admin/facilities/${uuid}`);
    },

    createFacility(data) {
        return client.post("/admin/facilities", data, {
            headers: { "Content-Type": "multipart/form-data" },
        });
    },

    updateFacility(uuid, data) {
        return client.post(`/admin/facilities/${uuid}`, data, {
            headers: { "Content-Type": "multipart/form-data" },
        });
    },

    getFacilityBlockedSlots(uuid) {
        return client.get(`/admin/facilities/${uuid}/blocked-slots`);
    },

    createFacilityBlockedSlot(uuid, data) {
        return client.post(`/admin/facilities/${uuid}/blocked-slots`, data);
    },

    deleteFacilityBlockedSlot(uuid, slotId) {
        return client.delete(
            `/admin/facilities/${uuid}/blocked-slots/${slotId}`,
        );
    },

    // Bookings
    getBookings(params = {}) {
        return client.get("/admin/bookings", { params });
    },

    getBooking(uuid) {
        return client.get(`/admin/bookings/${uuid}`);
    },

    approveBooking(uuid) {
        return client.post(`/admin/bookings/${uuid}/approve`);
    },

    rejectBooking(uuid, data) {
        return client.post(`/admin/bookings/${uuid}/reject`, data);
    },

    cancelBooking(uuid, data = {}) {
        return client.post(`/admin/bookings/${uuid}/cancel`, data);
    },

    exportBookings(params = {}) {
        return client.get("/admin/bookings/export", {
            params,
            responseType: "blob",
        });
    },

    // Notifications
    getNotifications(params = {}) {
        return client.get("/admin/notifications", { params });
    },

    createNotification(data) {
        return client.post("/admin/notifications", data);
    },

    publishNotification(uuid) {
        return client.post(`/admin/notifications/${uuid}/publish`);
    },

    archiveNotification(uuid) {
        return client.post(`/admin/notifications/${uuid}/archive`);
    },

    // Properties
    getProperties() {
        return client.get("/admin/properties");
    },

    getBlocks(propertyUuid) {
        return client.get(`/admin/properties/${propertyUuid}/blocks`);
    },

    getUnits(blockUuid) {
        return client.get(`/admin/blocks/${blockUuid}/units`);
    },

    // Settings
    getSettings() {
        return client.get("/admin/settings");
    },

    updateSettings(data) {
        return client.put("/admin/settings", data);
    },
};
