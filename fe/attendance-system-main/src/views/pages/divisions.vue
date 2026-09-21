<template>
<div>
    <div class="grid grid-rows-1">
        <div v-if="divisions.length > 0 && !isLoading" class="grid lg:grid-cols-5 grid-cols-1 gap-2">
            <div 
                v-for="(division, i) in divisions" 
                :key="i"
                @click="$router.push(`/divisions/${division.id}`)"
            >
                <box-list :element="division" />
            </div>
        </div>

        <div v-else-if="divisions.length === 0 && !isLoading" class="flex justify-center">
            <span>Tidak ada data Hubungi Admin Terkait</span>
        </div>
        
        <page-loader v-else />
    </div>
</div>
</template>

<script setup>
import BoxList from '@/components/Card/box-list.vue';
import divisionApi from '@/helpers/division.js';
import { onMounted, ref, computed } from 'vue';
import pageLoader from '@/components/Loader/pageLoader.vue';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const user = computed(() => JSON.parse(localStorage.getItem('users')));
const roles = computed(() => user?.value?.roles);
const router = useRouter();
const toast = useToast();

const divisionsIds = ref();

const divisions = ref([]);
const isLoading = ref(false);
const getData = () => {
    const params = {
        division_ids: divisionsIds?.value,
        status: 'publish',
        entities: 'users.user.profile.medias',
		isMyDivision: true,
    }
    const callback = (response) => {
        const res = response?.data?.data;
        const data = res?.map(curr => ({
            ...curr,
            assignto: curr?.users?.map(user => ({
                name: user?.user?.name,
                image: curr?.user?.profile?.medias?.url
            })),
        }));
        divisions.value = data;
    };

    const err = (e) => {
        console.log('e => ', e);
    };

    divisionApi.getData(params, callback, err);
};
const checkCapabilities = () => {
    getData();
	const checkIsUserAdmin = roles?.value?.map(role => role?.role?.name).includes('user_admin');
	const checkIsUser = roles?.value?.map(role => role?.role?.name).includes('user');
	const checkIsUserAccebility = checkIsUserAdmin || checkIsUser;
	if (!checkIsUserAccebility) {
        localStorage.removeItem('token');
		router.push('/login');
		toast?.error('Anda Memiliki Role yang tidak bisa masuk ke halaman ini, untuk lebih lanjut hubungi Admin');
	}
};

onMounted(() => {
    checkCapabilities();
});
</script>

<style>

</style>
