import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import authRoutes from './auth';
import residentRoutes from './resident';
import guardRoutes from './guard';
import adminRoutes from './admin';

const routes = [
    ...authRoutes,
    ...residentRoutes,
    ...guardRoutes,
    ...adminRoutes,
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/NotFound.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.fetchUser();
    }

    if (to.meta.guest) {
        if (auth.isAuthenticated) {
            return auth.getHomePath();
        }
        return;
    }

    if (to.meta.requiresAuth) {
        if (!auth.isAuthenticated) {
            return { name: 'login', query: { redirect: to.fullPath } };
        }

        if (to.meta.requiresApproval && !auth.isApproved) {
            if (auth.isPending) return { name: 'pending-approval' };
            if (auth.isRejected) return { name: 'rejected' };
            return { name: 'login' };
        }

        if (to.meta.roles) {
            const userRole = auth.userRole;
            if (!to.meta.roles.includes(userRole)) {
                return auth.getHomePath();
            }
        }
    }
});

export default router;
