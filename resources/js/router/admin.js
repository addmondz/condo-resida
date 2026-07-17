const AdminLayout = () => import('@/layouts/AdminLayout.vue');

const meta = { requiresAuth: true, requiresApproval: true, roles: ['super_admin', 'property_admin'] };

export default [
    {
        path: '/admin',
        component: AdminLayout,
        meta,
        children: [
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: () => import('@/pages/admin/Dashboard.vue'),
            },
            {
                path: 'users',
                name: 'admin.users',
                component: () => import('@/pages/admin/users/Index.vue'),
            },
            {
                path: 'users/:uuid',
                name: 'admin.users.show',
                component: () => import('@/pages/admin/users/Show.vue'),
                props: true,
            },
            {
                path: 'visitors',
                name: 'admin.visitors',
                component: () => import('@/pages/admin/visitors/Index.vue'),
            },
            {
                path: 'visitors/:uuid',
                name: 'admin.visitors.show',
                component: () => import('@/pages/admin/visitors/Show.vue'),
                props: true,
            },
            {
                path: 'facilities',
                name: 'admin.facilities',
                component: () => import('@/pages/admin/facilities/Index.vue'),
            },
            {
                path: 'facilities/create',
                name: 'admin.facilities.create',
                component: () => import('@/pages/admin/facilities/Create.vue'),
            },
            {
                path: 'facilities/:uuid/edit',
                name: 'admin.facilities.edit',
                component: () => import('@/pages/admin/facilities/Edit.vue'),
                props: true,
            },
            {
                path: 'bookings',
                name: 'admin.bookings',
                component: () => import('@/pages/admin/bookings/Index.vue'),
            },
            {
                path: 'bookings/:uuid',
                name: 'admin.bookings.show',
                component: () => import('@/pages/admin/bookings/Show.vue'),
                props: true,
            },
            {
                path: 'notifications',
                name: 'admin.notifications',
                component: () => import('@/pages/admin/notifications/Index.vue'),
            },
            {
                path: 'notifications/create',
                name: 'admin.notifications.create',
                component: () => import('@/pages/admin/notifications/Create.vue'),
            },
            {
                path: 'settings',
                name: 'admin.settings',
                component: () => import('@/pages/admin/Settings.vue'),
            },
        ],
    },
];
