const ResidentLayout = () => import("@/layouts/ResidentLayout.vue");

const meta = {
    requiresAuth: true,
    requiresApproval: true,
    roles: ["resident"],
};

export default [
    {
        path: "/resident",
        component: ResidentLayout,
        meta,
        children: [
            {
                path: "dashboard",
                name: "resident.dashboard",
                component: () => import("@/pages/resident/Dashboard.vue"),
            },
            {
                path: "profile",
                name: "resident.profile",
                component: () => import("@/pages/resident/Profile.vue"),
            },
            {
                path: "visitors",
                name: "resident.visitors",
                component: () => import("@/pages/resident/visitors/Index.vue"),
            },
            {
                path: "visitors/create",
                name: "resident.visitors.create",
                component: () => import("@/pages/resident/visitors/Create.vue"),
            },
            {
                path: "visitors/:uuid",
                name: "resident.visitors.show",
                component: () => import("@/pages/resident/visitors/Show.vue"),
                props: true,
            },
            {
                path: "facilities",
                name: "resident.facilities",
                component: () =>
                    import("@/pages/resident/facilities/Index.vue"),
            },
            {
                path: "facilities/:uuid",
                name: "resident.facilities.show",
                component: () => import("@/pages/resident/facilities/Show.vue"),
                props: true,
            },
            {
                path: "bookings",
                name: "resident.bookings",
                component: () => import("@/pages/resident/bookings/Index.vue"),
            },
            {
                path: "bookings/create/:facilityUuid?",
                name: "resident.bookings.create",
                component: () => import("@/pages/resident/bookings/Create.vue"),
                props: true,
            },
            {
                path: "bookings/:uuid",
                name: "resident.bookings.show",
                component: () => import("@/pages/resident/bookings/Show.vue"),
                props: true,
            },
            {
                path: "notifications",
                name: "resident.notifications",
                component: () => import("@/pages/resident/Notifications.vue"),
            },
        ],
    },
];
