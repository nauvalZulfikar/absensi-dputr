<template></template>

<script setup>
import authApi from '@/helpers/auth';
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const router = useRouter();
const toast = useToast();
const logout = () => {
    const callback = (res) => {
        if (res?.data?.meta?.status) {
            router.push('/login');
            toast.success('Berhasil Logout');
        }
    };
    const err = (e) => {
        console.log("🚀 ~ file: Logout.vue:10 ~ err ~ e:", e);
    };

    authApi.logout({}, callback, err);
};

onMounted(() => {
    logout();
});
</script>
