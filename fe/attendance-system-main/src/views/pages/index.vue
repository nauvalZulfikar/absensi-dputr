<template>
    <div>
        <div class="my-3">
            <VueAllert type="success" dismissible icon="akar-icons:double-check">
                Selamat Datang {{ user?.name }}
            </VueAllert>
        </div>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div class="">
                <card className="h-min">
                    <div class="text-center">
                        <div class="flex justify-center">
                            <div class="flex-0">
                                <div class="author-img w-[65px] h-[88px] rounded-[40px]">
                                    <img :src="media?.url ?? userDummyImage"
                                        class="w-full h-full object-cover rounded-md" />
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="">
                            <div>
                                <span class="text-sm  whitespace-nowrap sm:whitespace-normal font-bold sm:text-xl">
                                    {{ user?.name }}
                                </span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-bold">ID : </span> <span>{{ user?.profile_nik ?? '-' }}</span>
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-bold">Divisi </span>
                                <div class="mt-2">
                                    <ul>
                                        <li v-for="(division, index) in divisions" :key="index">
                                            <vue-badge :label="division?.division?.name" badge-class="mt-2" />
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </card>
                <div class="grid grid-cols-1 sm:grid-cols-2 my-4 gap-4">
                    <vue-button text="Change Password" btn-class="btn-sm btn-primary light"
                        @click="$router.push('/change-password')" />
                    <vue-button text="Change Profile" btn-class="btn btn-success light btn-sm"
                        @click="$router.push('/on-boarding')" />
                </div>
            </div>
            <div class="col-span-1 sm:col-span-2">
                <card>
                    <div>
                        <div class="">
                            <span class="whitespace-nowrap text-sm font-bold sm:text-xl">
                                Quick Attendance
                            </span>
                        </div>
                        <hr class="my-4">
                        <div v-if="!isProjectFetching && projects.length > 0">
                            <div v-for="(project, i) in projects" :key="i" class="mt-6">
                                <header-project :element="project" />
                                <div class="sm:px-2 gap-2">
                                    <div class="grid grid-cols-8">
                                        <div>
                                            <span class="font-bold whitespace-nowrap text-sm">
                                                Time In :
                                            </span>
                                            <span class="text-sm">
                                                {{ convertToShortFormat(project?.shift?.timeIn) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="font-bold whitespace-nowrap text-sm">
                                                Time Out :
                                            </span>
                                            <span class="text-sm">
                                                {{ convertToShortFormat(project?.shift?.timeOut) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="font-bold whitespace-nowrap text-sm">
                                        Deadline : <span class="font-normal">{{ dayjs(project.targetdate).format('dddd, D MMMM YYYY') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
                                        <vue-button text="Clock In" btn-class="btn btn-sm btn-success light"
                                            @click="setType('clockin', project, project?.shift_id)" />
                                        <vue-button text="Clock out" btn-class="btn btn-sm btn-danger light"
                                            @click="setType('clockout', project, project?.shift_id)" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!isProjectFetching && projects.length === 0">
                            <div class="flex justify-center">
                                <span>
                                    Tidak ada data
                                </span>
                            </div>
                        </div>
                        <pageLoader v-else />
                    </div>
                </card>
            </div>
        </div>
    </div>
</template>

<script setup>
import dayjs from 'dayjs';
import card from '@/components/Card';
import HeaderProject from '@/components/Project/Header.vue';
import VueButton from '@/components/Button/index.vue';
import VueAllert from '@/components/Alert';
import { useUserStore } from '@/store/user';
import { computed, watch, ref, onMounted } from 'vue';
import VueBadge from '@/components/Badge/index.vue';
import pageLoader from '@/components/Loader/pageLoader.vue';
import projectApi from '@/helpers/projects';
import { useRouter } from 'vue-router';
import { userDummyImage } from "@/constant/static";
import { getDataShifts } from '@/helpers/shift';

const userStore = useUserStore();

const user = computed(() => userStore.user);
const media = computed(() => user?.value?.profile?.medias);
const divisions = computed(() => user?.value?.divisions?.filter(curr => curr.type === 'assign' && curr?.division?.status === 'publish'));
const projects = ref('');
const router = useRouter();

const isProjectFetching = ref(false);

const setType = (type, project, shiftId) => {
    router.push(`/attendance/${type}/${project?.id}?shift_id=${shiftId}`)
};

const getDataMyShift = () => {
    const params = {
        is_myshift: true,
        entities: 'projectShift.project.shift, projectShift.project.division, userShift',
    };
    const callback = (res) => {
        const shifts = res?.data?.data;
        projects.value = shifts?.map(curr => ({
            ...curr?.project_shift?.[0]?.project,
            shift: {
                timeIn: curr?.timeIn,
                timeOut: curr?.timeOut,
                type: curr?.type,
            },
            shift_id: curr?.id,
        })).filter(curr => curr?.division?.status === 'publish');
    };
    const err = () => { 
        console.log(e);
    };
    getDataShifts(params, callback, err)
};

const checkCapabilities = () => {
    const user = JSON.parse(localStorage.getItem('users'));
    const roleCanAccess = [];

    const checkRoles = user?.roles?.some(curr => roleCanAccess.includes(curr?.role?.name));

    if(checkRoles) {
        router?.push('/admin/division');
    }

    if (!user?.profile) {
        router.push('/on-boarding');
    }
};

watch(user, (newValue, oldValue) => {
    if (newValue && newValue.id !== undefined) {
        // getMyProjects();
    } else {
        checkCapabilities();
    }
});

const convertToShortFormat = (fullTime) => {
    const [hours, minutes] = fullTime.split(':');
    return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
};

onMounted(() => {
    checkCapabilities();
    getDataMyShift();
});

</script>
