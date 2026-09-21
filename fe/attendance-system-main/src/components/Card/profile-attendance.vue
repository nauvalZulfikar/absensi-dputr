<template>
	<Card>
		<div class="flex-col flex md:flex-row items-center">
			<div class="flex-0 mr-6">
				<div class="author-img w-[65px] h-[88px] rounded-[40px]">
					<img :src="profile?.profile?.medias?.url ?? userDummyImage" alt="" class="w-full h-full object-cover" />
				</div>
			</div>
			<div class="flex-1 text-center md:text-start mt-2">
				<div class="mb-4 capitalize text-base">
					{{  profile.name  }}
					<vue-badge
						label="Penanggung Jawab"
						badgeClass="bg-primary-500 text-sm text-primary-500 bg-opacity-[0.12]" />
				</div>
				<div class="grid sm:grid-cols-5 grid-cols-1 sm:gap-0 gap-4">
					<div v-for="(field, index) in fields" :key="index">
						<div class="text-sm">{{ field }}</div>
						<div class="text-sm font-bold">
							{{ valueField[field] }}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="grid grid-cols-1 gap-5 mt-6">
			<div class="xl:col-span-8 col-span-12">
				<div
					class="grid md:grid-cols-5 sm:grid-cols-2 grid-cols-2 gap-3">
					<div
						v-for="(item, i) in statistics"
						:key="i"
                        :class="[item.bg, {'shadow-lg bg-opacity-50':item.key === route.query.summary}]"
						class="`rounded-md p-2 bg-opacity-[0.15] dark:bg-opacity-50 text-center transition-all duration-300 hover:shadow-lg hover:bg-opacity-50 cursor-pointer`"
                        @click="setFilterSummary(item.key)"
                    >
						<div
							class="mx-auto h-6 w-6 flex items-center justify-center rounded-full bg-white text-lg mb-2"
							:class="item.text">
							<Icon :icon="item.icon"
                        />
						</div>
						<span
							class="block text-xs text-slate-600 font-medium dark:text-white"
							>{{ item.title }}</span
						>
						<span
                            v-if="!isLoading"
							class="block text-sm text-slate-900 dark:text-white font-medium"
						>
                            {{ item.count }}
                        </span>
                        <page-loader v-else customClass="mx-auto h-8 w-8" />
					</div>
				</div>
			</div>
		</div>
	</Card>
</template>

<script setup>
import Card from '@/components/Card';
import VueBadge from '@/components/Badge/index.vue';
import Icon from '@/components/Icon';
import {getDataSummary} from '@/helpers/attendances.js';
import { useUserStore } from '@/store/user';
import { computed, onBeforeMount, ref, watchEffect } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import PageLoader from '@/components/Loader/pageLoader.vue'
import pageLoader from '@/components/Loader/pageLoader.vue';
import { userDummyImage } from '@/constant/static';

const props = defineProps({
	type: {
		type: String,
		default: 'attendance-log',
	},
});

const userStore = useUserStore();
const route = useRoute();
const router = useRouter();
const profile = computed(() => userStore.user);
const isLoading = ref(false);
const attendanceSummary = ref([
	{
        key: 'all',
		title: 'Total Attendance',
		count: '64',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
	{
        key: 'clockin',
		count: '45',
		title: 'ClockIn',
		bg: 'bg-success-500',
		text: 'text-success-500',
		percent: '8.67%',
		icon: 'heroicons-outline:chart-pie',
	},
	{
        key: 'clockout',
		title: 'ClockOut',
		count: '190',
		bg: 'bg-primary-500',
		text: 'text-primary-500',
		percent: '1.67%  ',
		icon: 'heroicons-outline:clock',
	},
	{
        key: 'late',
		title: 'Late',
		count: '$3,564',
		bg: 'bg-warning-500',
		text: 'text-warning-500',
		percent: '11.67%  ',
		icon: 'heroicons-outline:calculator',
	},
	{
        key: 'lembut',
		title: 'Lembur',
		count: '$3,564',
		bg: 'bg-warning-500',
		text: 'text-warning-500',
		percent: '11.67%  ',
		icon: 'heroicons-outline:calculator',
	},
]);

const userSummary = ref([
{
        key: 'all',
		title: 'Total Project',
		count: '64',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
	{
        key: 'in-progress',
		count: '45',
		title: 'In Progress',
		bg: 'bg-success-500',
		text: 'text-success-500',
		percent: '8.67%',
		icon: 'heroicons-outline:chart-pie',
	},
	{
        key: 'done',
		title: 'Done',
		count: '$3,564',
		bg: 'bg-warning-500',
		text: 'text-warning-500',
		percent: '11.67%  ',
		icon: 'heroicons-outline:calculator',
	},
	{
        key: 'lembut',
		title: 'Lembur',
		count: '$3,564',
		bg: 'bg-warning-500',
		text: 'text-warning-500',
		percent: '11.67%  ',
		icon: 'heroicons-outline:calculator',
	},
]);

const statistics = ref([]);

const getDataAttendanceSummary = () => {
	isLoading.value = true;
    const params = {
		projectId: route.params.project_id,
    }
    const callback = (response) => {
		isLoading.value = false;
        if (response.data.meta.status) {
			const data = response.data.data;
            statistics.value[0].count = data.all;
            statistics.value[1].count = data.clockin;
            statistics.value[2].count = data.clockout;
            statistics.value[3].count = data.late;
            statistics.value[4].count = data?.overtime;
        }
    };
	
    const err = (e) => {
		console.log(e);
    }
	
    getDataSummary(params, callback, err);
};

const init = () => {
	if (props.type === 'attendance-log') {
		statistics.value = attendanceSummary.value;
	}
};

const setFilterSummary = (key) => {
	router.replace({ query: { ...route.query, summary: key } });
};

const fetchSummary = () => {
	if (props.type === 'attendance-log') {
		getDataAttendanceSummary();
	}
};
const effect = watchEffect(() => {
    fetchSummary();
	init();
});


onBeforeMount(() => {
    effect(); 
});


const fields = ['Project', 'Phone', 'Email Address', 'Divisi', 'Lokasi'];

const valueField = {
	Project: 'Project Name',
	Phone: '620876557892',
	'Email Address': profile.value.email,
	Divisi: 'Jalan RAYA',
	Lokasi: 'Jalan Jakarta, Bandung',
};
</script>

<style></style>
