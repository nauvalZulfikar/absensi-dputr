const router = [
    {
        name: 'Error',
        path: '/error',
        component: () => import('@/views/error.vue'),
    },
    {
        name: 'AdminPage',
        path: '/admin',
        component: () => import('@/Layout/AdminPage.vue'),
        children: [
            {
                name: 'Admin Project Dashboard',
                path: 'dashboard',
                component: () => import('@/views/dashboard/project.vue'),
                meta: {
                    groupParent: 'Home Dashboard',
                    showDatePicker: true,
                    requiresAuth: true,
                },
            },
            {
                name: 'Project',
                path: 'project',
                component: () => import('@/views/pages/admin/project.vue'),
                meta: {
                    groupParent: 'Project List',
                    showDatePicker: true,
                    requiresAuth: true,
                },
            },
            {
                name: 'Attendance List',
                path: 'attendance-list',
                component: () => import('@/views/pages/admin/attendance-list.vue'),
                meta: {
                    groupParent: 'Attendance List',
                    showDatePicker: true,
                    requiresAuth: true,
                }
            },
            {
                name: 'User',
                path: 'user',
                component: () => import('@/views/pages/admin/user.vue'),
                meta: {
                    groupParent: 'User List',
                    showDatePicker: true,
                    requiresAuth: true,
                }
            },
            {
                name: 'Admin Division',
                path: 'division',
                component: () => import('@/views/pages/admin/division.vue'),
                meta: {
                    groupParent: 'List Division',
                    showDatePicker: true,
                    requiresAuth: true,
                }
            },
            {
                name: 'Admin Tambah User',
                path: ':type/:id/:slug',
                component: () => import('@/views/pages/admin/_add-user/index.vue'),
                meta: {
                    groupParent: 'List Division',
                    showDatePicker: true,
                    requiresAuth: true,
                }
            },
            {
                name: 'Admin File Manager',
                path: 'file-manager',
                component: () => import('@/views/pages/admin/file-manager.vue'),
                meta: {
                    groupParent: 'File Manager',
                    showDatePicker: false,
                    requiresAuth: true,
                }
            }
        ],
    }
];

export default router;
