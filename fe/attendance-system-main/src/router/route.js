import adminRoute from '@/router/admin-dashboard/index.js'
const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/login/index.vue'),
  },
  {
    path: '/logout',
    name: 'logout',
    component: () => import('@/Layout/Logout.vue'),
  },
  {
    path: "/admin",
    name: "Layout",
    redirect: "/",
    component: () => import("@/Layout/index.vue"),
    children: [
      ...adminRoute,
      {
        name: 'Home',
        path: '/',
        component: () => import('@/views/pages/index.vue'),
        meta: {
          breadcrumb: [],
          requiresAuth: true,
        },
      },
      {
        name: 'List Divisi',
        path: '/divisions',
        component: () => import('@/views/pages/divisions'),
        meta: {
          requiresAuth: true,
        }
      },
      {
        name: 'List Project',
        path: '/divisions/:division_id',
        component: () => import('@/views/pages/projects.vue'),
        meta: {
          groupParent: 'List Projects',
          requiresAuth: true,
        },
      },
      {
        path: '/detail-project/:id',
        name: 'detail-project',
        component: () => import('@/views/pages/detail-project.vue'),
        meta: {
          groupParent: 'Project',
          requiresAuth: true,
        }
      },
      {
        path: '/attendance/:type/:project_id',
        name: 'Attendance',
        component: () => import('@/views/pages/attendance.vue'),
        meta: {
          requiresAuth: true,
        }
      },
      {
        path: '/attendance-log/division/:division_id/project/:project_id',
        name: 'Attendance Logs',
        component: () => import('@/views/pages/attandance-log.vue'),
        meta: {
          requiresAuth: true,
        }
      },
      {
        path: '/on-boarding',
        name: 'OnBoarding',
        component: () => import('@/views/pages/on-boarding.vue'),
        meta: {
          requiresAuth: true,
        }
      },
      {
        path: '/change-password',
        name: 'ChangePassword',
        component: () => import('@/views/pages/change-password.vue'),
        meta: {
          requiresAuth: true,
        }
      },
      
    ],
  },
];

export default routes;
