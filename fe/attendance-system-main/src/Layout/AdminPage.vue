<template>
    <div>
        <router-view />
    </div>
</template>

<script setup>
import { useUserStore } from '@/store/user';
import { computed, onMounted, ref, watch } from 'vue';
import userApi from '@/helpers/user';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { routeAccessRules } from '@/constant/static';
const userStore = useUserStore();

const router = useRouter();
const route = useRoute();
const toast = useToast();
const roles = computed(() => userStore?.user?.roles);
const isLoading = ref(false)

const checkAccessForRoute = (roles, restrictedRole, router, toast) => {
    const checkAccess = roles?.value?.some(curr => curr?.role?.name === restrictedRole && !routeAccessRules[route?.path]?.[restrictedRole]);

    if (checkAccess) {
        router.back();
        toast.error('Maaf, akun Anda saat ini tidak memiliki akses');
    }
};

const checkCapabilities = () => {
    const roleCanAccess = ['admin', 'superadmin'];

    const checkRoles = roles?.value?.some(curr => roleCanAccess.includes(curr?.role?.name));

    const restrictedRoles = routeAccessRules[route?.path];

    if (restrictedRoles) {
        for (const restrictedRole in restrictedRoles) {
            checkAccessForRoute(roles, restrictedRole, router, toast);
        }
    }

    if (!checkRoles) {
        router.push('/');
        toast.error('Maaf, akun Anda saat ini tidak memiliki akses');
    }
};




const getUser = () => {
    isLoading.value = true;
    const callback = (response) => {
        isLoading.value = false;
        const users = response?.data?.data;
        localStorage.setItem('users', JSON.stringify(users));
        userStore.setUser(users);
    };
    const err = (e) => {
        console.log(e);
    };

    userApi.getMe({
        entities: 'roles.role,profile.medias,divisions.division'
    }, callback, err);
};

watch(() => userStore?.user, (value) => {
    if (value) {
        checkCapabilities();
    }
})

watch(() => route.path, (value) => {
    if (value) {
        checkCapabilities();
    }
})

// Hook API
onMounted(() => {
    getUser();
})
</script>
