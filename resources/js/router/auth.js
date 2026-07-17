const AuthLayout = () => import('@/layouts/AuthLayout.vue');

export default [
    {
        path: '/',
        redirect: '/login',
    },
    {
        path: '/login',
        component: AuthLayout,
        children: [
            {
                path: '',
                name: 'login',
                component: () => import('@/pages/auth/Login.vue'),
                meta: { guest: true },
            },
        ],
    },
    {
        path: '/register',
        component: AuthLayout,
        children: [
            {
                path: '',
                name: 'register',
                component: () => import('@/pages/auth/Register.vue'),
                meta: { guest: true },
            },
        ],
    },
    {
        path: '/forgot-password',
        component: AuthLayout,
        children: [
            {
                path: '',
                name: 'forgot-password',
                component: () => import('@/pages/auth/ForgotPassword.vue'),
                meta: { guest: true },
            },
        ],
    },
    {
        path: '/reset-password',
        component: AuthLayout,
        children: [
            {
                path: '',
                name: 'reset-password',
                component: () => import('@/pages/auth/ResetPassword.vue'),
                meta: { guest: true },
            },
        ],
    },
    {
        path: '/pending-approval',
        name: 'pending-approval',
        component: () => import('@/pages/auth/PendingApproval.vue'),
        meta: { requiresAuth: true },
    },
    {
        path: '/rejected',
        name: 'rejected',
        component: () => import('@/pages/auth/Rejected.vue'),
        meta: { requiresAuth: true },
    },
];
