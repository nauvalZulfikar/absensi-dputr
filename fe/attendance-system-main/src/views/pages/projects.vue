<template>
	<div>
		<div class="grid grid-rows-1">
			<div
				v-if="projects.length > 0 && !isLoading"
				class="grid lg:grid-cols-5 grid-cols-1 gap-2"
            >
				<div
					v-for="(project, i) in dataProjects"
					:key="i"
					@click="$router.push(`/detail-project/${project.id}`)"
				>
					<box-list :element="project" type="project-list" />
				</div>
			</div>
            <div v-else-if="isLoading">
                <page-loader />
            </div>
			<div v-else class="flex justify-center">
				<div class="">
					<span>Tidak ada Data hubungi Admin Terkait</span>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import projectApi from '@/helpers/projects';
import { computed, onMounted, ref, watchEffect } from 'vue';
import { useRoute } from 'vue-router';
import PageLoader from '@/components/Loader/pageLoader.vue'
import BoxList from '@/components/Card/box-list.vue';

const projects = ref([]);
const isLoading = ref(false);

const route = useRoute();
const dataProjects = computed(() => {
	return projects?.value?.map(project => ({
		...project,
		name: project?.name ?? 'Name',
		progress: 69,
		assignto: project?.users?.map(user => ({
			name: user?.user?.name,
			image: project?.user?.profile?.medias?.url
		})),
	}))
})
// Fetch data from API
const params = computed(() => ({
	division_id: route.params.division_id,
	status: 'publish',
	entities: 'users',
	is_my_project: true,
}));
const getData = () => {
    isLoading.value = true;
	const callback = (response) => {
        isLoading.value = false;
		projects.value = response.data.data;
	};

	const err = (e) => {
        isLoading.value = false;
		console.log('e => ', e);
	};

	projectApi.getDataProjects(params.value, callback, err);
};

onMounted(() => {
	getData();
});
</script>

<style></style>
