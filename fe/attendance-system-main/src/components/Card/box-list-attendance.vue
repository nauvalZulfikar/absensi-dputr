<template>
	<Card title="Riwayat Kehadiran">
		<div v-if="attendances.length > 0 && !isLoading" class="grid sm:grid-cols-4 grid-cols-1 gap-3">
			<card
				v-for="(attendance, index) in attendances"
				:key="index"
				className="card-history bg-gradient-to-br from-gray-100 to-gray-200 hover:shadow-lg hover:transform hover:scale-105 transition-transform cursor-pointer p-1"
                @onClick="showModal(attendance)"
            >
				<div class="flex justify-between items-center p-1">
					<div class="flex items-center space-x-1">
						<icon
							icon="carbon:time"
							class="text-gray-600 text-xs" 
                        />
						<span class="text-gray-600 text-xs font-bold">{{ attendance.date }}</span>
					</div>
					<div class="flex space-x-1">
						<vue-badge
							:label="attendance.type"
							:badgeClass="`text-xs ${setBadgeColor(attendance.type)} text-white bg-opacity-75 badge-type`" 
                        />
						<vue-badge
							:label="attendance?.status ?? 'late'"
							:badgeClass="`${setBadgeColor(attendance.status ?? 'late')} text-xs text-white bg-opacity-75 badge-status`" 
                        />
					</div>
				</div>
				<div class="mt-1 p-1">
					<div class="flex justify-between">
                        <div class="text-sm font-medium text-gray-600">
                            Waktu Kehadiran
                        </div>
                        <div class="text-sm font-bold">{{ attendance.time }}</div>
					</div>
				</div>
			</card>
		</div>

        <div v-if="attendances.length === 0 && !isLoading" class="flex justify-center">
            <span>
                Tidak ada data
            </span>
        </div>

        <page-loader v-if="isLoading" />

        <div class="p-4 mt-5 flex justify-center">
            <Pagination
                v-if="attendanceMeta && attendances.length > 0 && !isLoading"
                :current="page"
                :total="attendanceMeta.total"
                :per-page="paginate"
                @page-changed="page = $event"
            />
        </div>
	</Card>
</template>

<script setup>
import Card from '@/components/Card/index.vue';
// import dayjs from 'dayjs';
import Icon from '@/components/Icon';
import VueBadge from '@/components/Badge';
import { useToast } from 'vue-toastification';
import { useRoute } from 'vue-router';
import { getDataAttendanceLogs } from '@/helpers/attendances';
import Pagination from '@/components/Pagination'
import { computed, onBeforeUnmount, onMounted, ref, watchEffect } from 'vue';
import pageLoader from '../Loader/pageLoader.vue';
const toast = useToast();
const route = useRoute();

const emits = defineEmits(['show-modal']);


const showModal = (attendance) => {
    emits('show-modal', attendance);
};


const attendances = ref([]);
const isLoading = ref(false);
const attendanceMeta = ref(null);
const valuePaginate = window.innerWidth > 768 ? 8 : 2
const paginate = ref(valuePaginate);
const page = ref(1);
const summary = computed(() => route.query.summary);
const projectId = computed(() => route.params.project_id);

const getDataAttendanceLog = () => {
    isLoading.value = true;
	const params = {
        paginate: paginate.value,
        page: page.value,
        projectId: projectId.value,
        summary: summary.value,
		entities: 'project.division,user.profile.medias,media,user.roles.role,mediaProof,shift',
    };
	const callback = (response) => {
        if (response.data.meta.status) {
            isLoading.value = false;
            attendances.value = response.data.data;
            attendanceMeta.value = response.data.meta;
		}
	};
	const err = (e) => {
        isLoading.value = false;
        console.log(e);
	};

	getDataAttendanceLogs(params, callback, err);
};

const setBadgeColor = (type) => {
    let color = 'bg-success-500';
    if (type === 'clockout') {
        color = 'bg-primary-500'
    } else if (type === 'late') {
        color = 'bg-danger-500';
    }

    return color;
};

const effect = watchEffect(() => {
    getDataAttendanceLog();
});
onBeforeUnmount(() => {
    // Stopping the effect when the component is destroyed or when the effect is no longer needed
    effect();
})
</script>

<style lang="scss">
// .card-history {
//     &:hover {
//         .badge-type {
//             @apply hover:animate-bounce;
//         }
//     }
// }
</style>
