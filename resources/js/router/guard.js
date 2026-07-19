const GuardLayout = () => import("@/layouts/GuardLayout.vue");

const meta = { requiresAuth: true, requiresApproval: true, roles: ["guard"] };

export default [
    {
        path: "/guard",
        component: GuardLayout,
        meta,
        children: [
            {
                path: "dashboard",
                name: "guard.dashboard",
                component: () => import("@/pages/guard/Dashboard.vue"),
            },
            {
                path: "scanner",
                name: "guard.scanner",
                component: () => import("@/pages/guard/Scanner.vue"),
            },
            {
                path: "visitors",
                name: "guard.visitors",
                component: () => import("@/pages/guard/visitors/Index.vue"),
            },
            {
                path: "visitors/:uuid",
                name: "guard.visitors.show",
                component: () => import("@/pages/guard/visitors/Show.vue"),
                props: true,
            },
            {
                path: "activity-log",
                name: "guard.activity-log",
                component: () => import("@/pages/guard/ActivityLog.vue"),
            },
        ],
    },
];
