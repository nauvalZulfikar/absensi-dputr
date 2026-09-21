<template>
	<div>
		<Card bodyClass="p-0">
			<div class="p-6">
				<vue-allert v-if="typeAlert" :type="typeAlert" dismissible class="mb-4">
					{{ textAlert }}
				</vue-allert>
				<div class="items-center flex justify-between">
					<div class="flex justify-end gap-4 items-center">
						<vue-button
							text="Anggota"
							:btnClass="`${
								typeOption === 'list-member'
									? 'border-b border-primary-500 rounded-0'
									: ''
							} btn-sm`"
							@click="typeOption = 'list-member'" />
						<vue-button
							v-if="isPageDivision"
							text="Tambah Admin"
							:btnClass="`${
								typeOption === 'add-admin'
									? 'border-b border-primary-500 rounded-0'
									: ''
							} btn-sm`"
							@click="typeOption = 'add-admin'" />
						<vue-button
							text="Tambah Anggota atau Pengawas"
							:btnClass="`${
								typeOption === 'add-member'
									? 'border-b border-primary-500 rounded-0'
									: ''
							} btn-sm`"
							@click="typeOption = 'add-member'" />
					</div>
				</div>
				<hr class="my-6" />
				<ul v-if="!isLoading && listUsers.length > 0">
					<!-- Item 1 -->
					<li
						v-for="(user, i) in listUsers"
						:key="i"
						class="flex items-center justify-between space-x-4 py-2 border-b border-gray-300">
						<div class="flex items-center space-x-4">
							<span class="w-10 h-10 rounded-full flex-none">
								<img
									:src="user.img"
									:alt="userDummyImage"
									class="object-cover w-full h-full rounded-full" />
							</span>
							<div>
								<span class="text-sm font-bold">{{
									user.name
								}}</span>
								<div>
									<VueBadge :label="setNameRoles(user?.roles)" badgeClass="capitalize bg-primary-400 text-white text-sm" />
								</div>
							</div>
						</div>
						<div class="space-x-3">
							<vue-button
								v-if="typeOption === 'list-member'"
								btn-class="btn btn-sm"
								btnTooltip="Hapus Anggota"
								iconTooltip="Hapus Anggota"
								textClass="text-red-500"
								icon="heroicons-outline:trash"
								@click="deleteData(user, i)"
							/>
							<vue-button
								v-else
								btnTooltip="Masukan Anggota"
								btn-class="btn btn-sm btn-primary light"
								text="Pilih Untuk Bergabung"
								@click="insertUserProject(user, i)"
							/>
						</div>
					</li>
				</ul>
				<div v-else-if="!isLoading && listUsers.length === 0" class="flex justify-center">
					<span>
						Tidak Ada Akun yang berada di divisi Ini
					</span>
				</div>
				<pageLoader v-else />
			</div>
		</Card>
	</div>
</template>

<script setup>
import { useForm, useFieldArray } from 'vee-validate';
import VueButton from '@/components/Button';
import Card from '@/components/Card';
import { ref, watch, onMounted, computed, capitalize } from 'vue';
import userApi from '@/helpers/user';
import pageLoader from '@/components/Loader/pageLoader.vue';
import { useRoute } from 'vue-router';
import projectApi from '@/helpers/projects';
import { useToast } from 'vue-toastification';
import VueAllert from '@/components/Alert';
import divisionApi from '@/helpers/division';
import { setNameRoles } from "@/constant/helpers";
import VueBadge from '@/components/Badge/index.vue';
const userDummyImage =
	'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';
const props = defineProps({
	divisionId: {
		type: [String, Number],
		default: null,
	},
	projectId: {
		type: [String, Number],
		default: null,
	},
	users: {
		type: Array,
		default: () => [],
	},
});

const route = useRoute();
const toast = useToast();
const typeAlert = ref('');
const textAlert = ref('');

const isDeleteSuccess = ref(false);

const setNameOption = (option) =>
	option
		?.split('-')
		.map((word) => word.charAt(0).toUpperCase() + word.slice(1))
		.join(' ');

const typeOption = ref('list-member');
const isFetching = ref(false);
const isPageProjects = computed(() => route?.path?.includes('/project'));
const isPageDivision = computed(() => route?.path?.includes('/division'))
// const users = ref([]);

const roleIds = ref([]);
watch(
	() => typeOption?.value,
	(value) => {
		let temporaryProductId = isPageProjects.value ? props?.projectId : undefined;
		roleIds.value = [];
		if (value === 'add-admin') {
			temporaryProductId = undefined
			roleIds.value = [3];
		} else if (value === 'add-member') {
			temporaryProductId = undefined
			roleIds.value = [2,4];
		}
		getUserSelected(temporaryProductId);
	}
);

const listUsers = ref([]);
const isLoading = ref(false);
const userCount = ref(0);
const fetchParams = (projectId) => {
	let params;
	if (isPageProjects.value) {
		params = {
			division_ids: [props?.divisionId],
			entities: 'profile.medias,roles.role',
			roleIds: roleIds.value,
			// project_ids: [projectId],
			not_have_this_projects: [projectId],
		}
		if (typeOption?.value === 'list-member') {
			params = {
				division_ids: [props?.divisionId],
				entities: 'profile.medias,roles.role, projects',
				roleIds: roleIds.value,
				project_ids: [projectId],
				// not_have_this_projects: [22],
			}
		}
	} else {
		params = {
			entities: 'profile.medias,roles.role,divisions',
			roleIds: [],
		}
		if (typeOption?.value === 'list-member') {
			params = {
				...params,
				division_ids: [props?.divisionId],
				roleIds: [],
			}
		} else {
			params = {
				...params,
				roleIds: roleIds?.value,
				not_have_divisions: [props?.divisionId],
			}
		}
	}
	

	return params
};
const getUserSelected = (projectId) => {
	isLoading.value = true;
	const params = fetchParams(projectId)
	isFetching.value = true;
	const callback = (response) => {
		isLoading.value = false;
		if (response?.data?.meta?.status) {
			userCount.value = response?.data?.data;
			createUserDisplay(response?.data?.data);
		}
		isFetching.value = false;
	};
	const err = (e) => {
		console.error(e);
		isFetching.value = false;
	};

	userApi.getUserSelected(params, callback, err);
};

const deleteData = (user, index) => {
	const userId = user?.[isPageProjects.value ? 'projects' : 'divisions']?.id;
	const callback = (response) => {
		if (response?.data?.meta?.status) {
			const data = response?.data?.data;
			listUsers?.value?.splice(index, 1);
			isDeleteSuccess.value = true;
			typeAlert.value = 'danger';
			textAlert.value = 'Berhasil Menghapus Keanggotan ';
		}
	}
	const err = (e) => {
		console.log(e);
	};

	if (isPageProjects.value) {
		projectApi.deleteUserProject(userId, callback, err);
	} else {
		divisionApi.deleteUserProject(userId, callback, err)
	}
};

const insertUserProject = (user, index) => {
	const params = {
		user_id: user?.id,
		project_id: props?.projectId,
		division_id: props?.divisionId,
	};
	const callback = (response) => {
		if (response?.data?.meta?.status) {
			const data = response?.data?.data;
			listUsers?.value?.splice(index, 1);
			isDeleteSuccess.value = true;
			typeAlert.value = 'success';
			textAlert.value = 'Berhasil Menambahkan Keanggotan ';
		}
	}
	const err = (e) => {
		console.log(e);
	};
	if (isPageProjects.value) {
		projectApi.insertUserProject(params, callback, err);
	} else {
		divisionApi.insertUserProject(params, callback, err);
	}
};

const createUserDisplay = (users) => {
	listUsers.value = users?.map((user) => ({
		id: user?.id,
		name: user?.name,
		img: user?.profile?.medias?.url ?? userDummyImage,
		roles: user?.roles?.map((role) => role?.role?.name).join(' - '),
		projects: user?.projects?.find(curr => curr?.project_id === props?.projectId && curr?.user_id === user?.id),
		divisions: user?.divisions?.find(curr => curr?.devision_id === props?.divisionId && curr?.user_id === user?.id),
	}));
};

onMounted(() => {
	createUserDisplay();
	if (isPageProjects.value) {
		getUserSelected(props?.projectId);
	} else {
		getUserSelected();
	}
});
</script>
