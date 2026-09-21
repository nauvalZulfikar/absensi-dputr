<template>
	<div>
		<div>
			<profile-box type="attendance-log" />
		</div>
		<div class="mt-4">
			<box-list-attendance @show-modal="showModalDetail" />
		</div>
		<AttendanceDetailModal 
			:active-modal="isModalDetailVisible" 
			:data="attendance" 
			@close="isModalDetailVisible = false" 
		/>
	</div>
</template>

<script setup>
import ProfileBox from '@/components/Card/profile-attendance.vue';
import boxListAttendance from '@/components/Card/box-list-attendance.vue';
import AttendanceDetailModal from '@/components/Modal/AttendanceDetail.vue';
import { onMounted, ref } from 'vue';
import projectApi from '@/helpers/projects';
import { useRoute } from 'vue-router';

const isModalDetailVisible = ref(false);
const attendance = ref();
const route = useRoute();

const showModalDetail = (detail) => {
	isModalDetailVisible.value = true;
	attendance.value = {
		...detail,
		name: detail?.user?.name,
		project: detail?.project?.name,
		project_address: detail?.project?.address,
		project_decription: detail?.project?.description,
	};
	console.log("🚀 ~ showModalDetail ~ detail:", detail);
};

const getProject = () => {
	const id = route?.params?.project_id;
	const params = {
	};
	const callback = (res) => {
		if (res?.data?.data) {
			const data = res?.data?.data;
		}
	};
	const err = (e) => {};
	projectApi.getDetailProject(id, params,callback, err);
};

onMounted(() => {
	getProject();
});
</script>

<style></style>
