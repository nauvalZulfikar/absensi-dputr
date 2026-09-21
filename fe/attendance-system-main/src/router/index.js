import { createRouter, createWebHistory } from "vue-router";

import routes from "./route";

const router = createRouter({
  history: createWebHistory(import.meta.BASE_URL),
  base: import.meta.BASE_URL,
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

router.beforeEach((to, from, next) => {
  const titleText = to?.name;
  const words = titleText?.split(" ");
  const titlePage = words?.join(" ");
  document.title = "Absen  - " + titlePage;
  // 
  const token = localStorage.getItem('token');
  const isHaveRecord = to.matched.some((record) => record.meta.requiresAuth);
  if (isHaveRecord && token === '' && token === null) {
    next({
      path: '/login',
      query: { redirect: to.fullPath },
    });
  } else if (token && to.path === '/login') {
    router.push('/error');
  }

  next();
});


router.afterEach(() => {
  // Remove initial loading
  const appLoading = document.getElementById("loading-bg");
  if (appLoading) {
    appLoading.style.display = "none";
  }
});

export default router;
