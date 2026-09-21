<template>
<div>
    <card className="mb-2">
        <div class="grid grid-cols-6 gap-2">
            <BoxSummary :statistics="statistics" />
        </div>
    </card>
    <card>
        <data-table
            title="Attendance List"
            :is-loading="isLoading"
            isDateVisible
            is-export
            @set-date="setDate"
            @export-data="openModalDownload(exportLink)"
        />
    </card>
    <ModalAttendanceDetail 
        :activeModal="isAttendanceModalVisible" 
        :data="attendanceData" 
        @close="isAttendanceModalVisible = false" 
    />
    <DownloadFileModal 
        :active-modal="isDownloadVisible" 
        title="Export Data Kehadiran"
        :since="filterDate?.startDate"
        :until="filterDate?.endDate"
        @close="isDownloadVisible = false"
        @submit="exportData"
    />
</div>
</template>

<script setup>
import Card from '@/components/Card/index.vue';
import BoxSummary from '@/components/Card/box-summary.vue';
import DataTable from '@/components/DataTable/index.vue';
import ModalAttendanceDetail from '@/components/Modal/AttendanceDetail.vue';
import { computed, onMounted, ref, watch } from 'vue';
import { getAllData, getDataSummary } from "@/helpers/attendances";
import { useDataTableStore } from '@/store/data-table';
import { useRoute, useRouter } from 'vue-router';
import DownloadFileModal from '@/components/Modal/DownloadFIle.vue';
import { downloadFile } from '@/constant/helpers';
import dayjs from 'dayjs';
import {
    exportAttendance
} from '@/helpers/attendances';
const userDummyImage = 'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';

const date = {
    startDate: dayjs().format('YYYY-MM-DD'),
    endDate: dayjs().subtract(30, 'day').format('YYYY-MM-DD') 
}

const router = useRouter();
const route = useRoute();

const isLoading = ref(false);
const filterDate = ref(date);

const store = useDataTableStore();

const attendanceData = ref();
const isAttendanceModalVisible = ref(false);
const isDownloadVisible = ref(false);

const openModalDownload = () => {
    isDownloadVisible.value = !isDownloadVisible.value;
};

const statistics = ref([
    {
        key: 'all',
        title: "All",
        count: "0",
        bg: "bg-[#E5F9FF] dark:bg-slate-900	",
        text: "text-success-900",
        icon: "icon-park:data-all",
    },
    {
        key: 'clockin',
        title: "Absen Masuk",
        count: "3,564",
        bg: "bg-[#E5F9FF] dark:bg-slate-900	",
        text: "text-info-500",
        icon: "heroicons:shopping-cart",
    },
    {
        key: 'clockout',
        title: "Absen Keluar",
        count: "564",
        bg: "bg-[#FFEDE6] dark:bg-slate-900	",
        text: "text-warning-500",
        icon: "heroicons:cube",
    },
    {
        key: 'late',
        title: "Telat",
        count: "564",
        bg: "bg-[#FFEDE6] dark:bg-slate-900	",
        text: "text-warning-500",
        icon: "heroicons:cube",
    },
    {
        key: 'overtime',
        title: "Lembur",
        count: "0",
        bg: "bg-[#FFEDE6] dark:bg-slate-900	",
        text: "text-warning-500",
        icon: "heroicons:cube",
    },
]);

const headers = [
    { label: 'Name', field: 'name' },
	{ label: 'Project Name', field: 'project' },
	{ label: 'Full Address', field: 'full_address' },
	{ label: 'Roles', field: 'roles' },
	{ label: 'Type', field: 'type' },
	{ label: 'Status', field: 'status' },
];

const actions = [
    {
        key: 'draft',
		icon: 'fluent-mdl2:unpublish-content',
		tooltipText: 'Ubah Status menjadi draft/unpublish',
		btnClass: 'btn btn-sm text-danger-400',
	},
	{
        key: 'publish',
		icon: 'fluent-mdl2:publish-content',
		tooltipText: 'Ubah Status menjadi publish',
		btnClass: 'btn btn-sm text-success-600',
	},
];


const perPage = ref(10);
const currentPage = ref(1);

/**
 * Watches the `per_page` property in the `meta` object of the store and updates the `perPage` value accordingly.
 *
 * @param {Function} getter - A function that returns the value to be watched (e.g., `() => store?.meta?.per_page`).
 * @param {Function} callback - A callback function to be executed when the watched value changes.
 * @returns {void}
 */
watch(
    () => store?.meta?.per_page,
	(value) => {
        if (value) {
            perPage.value = value;
			getDataAttendance();
		}
	}
);

watch(
    () => store?.meta?.current_page,
	(value) => {
        if (value) {
            currentPage.value = value;
			getDataAttendance();
		}
	}
);
const meta = computed(() => store?.meta);
const summary = computed(() => route?.query?.summary);

const initQuery = () => {
    const query = route?.query;
    filterDate.value.startDate = query?.since;
    filterDate.value.endDate = query?.until;
};

const setDate = (date) => {
    filterDate.value = date;
    router?.replace({ query: { ...route?.query, since: dayjs(filterDate?.value?.startDate).format('YYYY-MM-DD'), until: dayjs(filterDate?.value?.endDate).format('YYYY-MM-DD') } })
    getDataAttendance();
    getDataAttendanceSummary();
};

watch(() => summary.value, (value) => {
    if (summary) {
        getDataAttendance();
    }
});

const fetchParams = computed(() => ({
    entities: 'project.division,user.profile.medias,media,user.roles.role,mediaProof,shift',
    admin_mode: true,
    paginate: perPage?.value,
    page: currentPage?.value,
    summary: summary.value,
    since: dayjs(filterDate?.value?.startDate).format('YYYY-MM-DD'),
    until: dayjs(filterDate?.value?.endDate).format('YYYY-MM-DD'),
}));

const getDataAttendance = () => {
    isLoading.value = true;
    const params = fetchParams?.value
    const callback = (res) => {
        const data = res?.data?.data;
        const meta = res?.data?.meta;
        isLoading.value = false;
        const configMapping = data?.map(curr => ({
            ...curr,
            image: curr?.user?.profile?.medias?.url ?? userDummyImage,
            name: curr?.user?.name,
            status: curr?.status ?? 'late',
            roles: curr?.user?.roles?.map(role => role?.role?.name) ?? [],
            project: curr?.project?.name,
            type: curr?.type,
            full_address: curr?.full_address ?? '-',
            project_address: curr?.project?.address,
            project_decription: curr?.project?.description,
            media_proof: curr?.media_proof?.url
        }))
        store?.setData(configMapping);
        store?.setMeta(meta);
    };
    const err = (e) => {
        console.log(e);
    }
    
    getAllData(params, callback, err);
};

const getDataAttendanceSummary = () => {
    const params = {
        admin_mode: true,
        since: dayjs(filterDate?.value?.startDate).format('YYYY-MM-DD'),
        until: dayjs(filterDate?.value?.endDate).format('YYYY-MM-DD'),
    };
    const callback = (res) => {
        const data = res?.data?.data;
        statistics.value[0].count = data?.all ?? 0;
        statistics.value[1].count = data?.clockin ?? 0;
        statistics.value[2].count = data?.clockout ?? 0;
        statistics.value[3].count = data?.late ?? 0;
        statistics.value[4].count = data?.overtime ?? 0;
    };
    const err = (e) => {
        console.log(e);
    }

    getDataSummary(params, callback, err);
};

watch(() => store?.typeAction, (value) => {
    console.log(value);
    if (value?.key === 'name-table') {
        attendanceData.value = value?.data;
        isAttendanceModalVisible.value = true;
    }
});

const exportData = () => {
    const params = {
        since: dayjs(filterDate?.value?.startDate).format('YYYY-MM-DD'),
        until: dayjs(filterDate?.value?.endDate).format('YYYY-MM-DD'),
        admin_mode: true,
    };

    const callback = (res) => {
        const data = res.data?.data;
        const exportLink = ref(`${import.meta.env.VITE_AUTH_API_URL}/export-data/${data?.file?.file_name}`);
        downloadFile(exportLink);
    }
    
    exportAttendance(params, callback);
};

onMounted(() => {
    initQuery();
    store?.setHeaders(headers);
    store?.setActions(actions);
    store?.setNameConfig(null);
    getDataAttendance();
    getDataAttendanceSummary();
});
</script>

<style>

</style>
