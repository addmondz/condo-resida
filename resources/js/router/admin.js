const AdminLayout = () => import("@/layouts/AdminLayout.vue");

const meta = {
    requiresAuth: true,
    requiresApproval: true,
    roles: ["super_admin", "property_admin"],
};

export default [
    {
        path: "/admin",
        component: AdminLayout,
        meta,
        children: [
            {
                path: "dashboard",
                name: "admin.dashboard",
                component: () => import("@/pages/admin/Dashboard.vue"),
            },
            {
                path: "users",
                name: "admin.users",
                component: () => import("@/pages/admin/users/Index.vue"),
            },
            {
                path: "users/create",
                name: "admin.users.create",
                component: () => import("@/pages/admin/users/Create.vue"),
            },
            {
                path: "users/:uuid",
                name: "admin.users.show",
                component: () => import("@/pages/admin/users/Show.vue"),
                props: true,
            },
            {
                path: "users/:uuid/edit",
                name: "admin.users.edit",
                component: () => import("@/pages/admin/users/Edit.vue"),
                props: true,
            },
            {
                path: "visitors",
                name: "admin.visitors",
                component: () => import("@/pages/admin/visitors/Index.vue"),
            },
            {
                path: "visitors/:uuid",
                name: "admin.visitors.show",
                component: () => import("@/pages/admin/visitors/Show.vue"),
                props: true,
            },
            {
                path: "facilities",
                name: "admin.facilities",
                component: () => import("@/pages/admin/facilities/Index.vue"),
            },
            {
                path: "facilities/create",
                name: "admin.facilities.create",
                component: () => import("@/pages/admin/facilities/Create.vue"),
            },
            {
                path: "facilities/:uuid",
                name: "admin.facilities.show",
                component: () => import("@/pages/admin/facilities/Show.vue"),
                props: true,
            },
            {
                path: "facilities/:uuid/edit",
                name: "admin.facilities.edit",
                component: () => import("@/pages/admin/facilities/Edit.vue"),
                props: true,
            },
            {
                path: "bookings",
                name: "admin.bookings",
                component: () => import("@/pages/admin/bookings/Index.vue"),
            },
            {
                path: "bookings/:uuid",
                name: "admin.bookings.show",
                component: () => import("@/pages/admin/bookings/Show.vue"),
                props: true,
            },
            {
                path: "notifications",
                name: "admin.notifications",
                component: () =>
                    import("@/pages/admin/notifications/Index.vue"),
            },
            {
                path: "notifications/create",
                name: "admin.notifications.create",
                component: () =>
                    import("@/pages/admin/notifications/Create.vue"),
            },
            {
                path: "notifications/:uuid",
                name: "admin.notifications.show",
                component: () =>
                    import("@/pages/admin/notifications/Show.vue"),
                props: true,
            },
            {
                path: "properties",
                name: "admin.properties",
                component: () =>
                    import("@/pages/admin/properties/Index.vue"),
            },
            {
                path: "properties/create",
                name: "admin.properties.create",
                component: () =>
                    import("@/pages/admin/properties/Create.vue"),
            },
            {
                path: "properties/:uuid",
                name: "admin.properties.show",
                component: () =>
                    import("@/pages/admin/properties/Show.vue"),
                props: true,
            },
            {
                path: "properties/:uuid/edit",
                name: "admin.properties.edit",
                component: () =>
                    import("@/pages/admin/properties/Edit.vue"),
                props: true,
            },
            {
                path: "settings",
                name: "admin.settings",
                component: () => import("@/pages/admin/Settings.vue"),
            },
        ],
    },
];
