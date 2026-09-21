<template>
	<div>
		<card>
			<data-table
				title="Divisi"
				:btn-text="textBtnCreate"
				:is-loading="isLoading"
				@open-modal-add="toogleModalForm('add')" 
			/>
		</card>
		<modal-form
			:active-modal="isModalForm"
			:fields="form"
			:type="typeForm"
			btn-text="Buat Divisi"
			@submit="submit"
			@close="close" 
        />
        
		<modal-confirm
            :activeModal="isModalConfirm"
            title="Publish Content"
            :text="textModal"
            @close="close"
            @submit="submitConfirm"
        />
		<ModalUserAssign 
            title="User Assignation"
			:active-modal="isModalAssignationVisible" 
			:divisionId="divisionId"
			@close="closeModalAssgn"  
		/>
	</div>
</template>

<script setup>
import Card from '@/components/Card/index.vue';
import DataTable from '@/components/DataTable/index.vue';
import divisionApi from '@/helpers/division.js';
import { useDataTableStore } from '@/store/data-table.js';
import { computed, onMounted, ref, watch } from 'vue';
import ModalForm from '@/components/Modal/Form.vue';
import userApi from '@/helpers/user.js';
import { createFormField } from '@/constant/helpers';
import ModalConfirm from '@/components/Modal/Confirm.vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/store/user';
import { useToast } from 'vue-toastification';
import ModalUserAssign from '@/components/Modal/UserAssignation.vue';
const router = useRouter();
const isLoading = ref(false);
const isModalAssignationVisible = ref(false);


const userDummyImage =
	'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';

const store = useDataTableStore();
const userStore = useUserStore();

const user = computed(() => JSON.parse(localStorage.getItem('users')));
const roles = computed(() => user?.value?.roles);
const divisionsIds = ref([]);

const toast = useToast();

const typeForm = ref('');

const headers = [
	{ label: 'Name', field: 'name' },
	{ label: 'Status', field: 'status' },
	{ label: 'Anggota', field: 'assign' },
	{ label: 'Actions', field: 'actions' },
];

const formConfig = [
	{
		type: 'text',
		key: 'name',
		label: 'Nama',
		placeholder: 'Masukan Nama',
	},
	{
		key: 'userids',
		type: 'multiselect',
		label: 'Yang Ditugaskan',
		multiple: true,
		placeholder: 'Masukan Akun yang di tugaskan',
	},
	{
		key: 'description',
		type: 'textarea',
		label: 'Description',
		placeholder: 'Masukan Description',
	},
];

const form = ref(formConfig.map(createFormField));
const divisionId = ref(null);

/**
 * Updates the form based on the provided form values and type.
 *
 * @param {Object} formValues - The values to update the form with.
 * @param {string} type - The type of the update ('update' or other).
 * @returns {void}
 */
const setFormUpdate = (formValues, type) => {
	const userFieldConfig = formConfig.find((field) => field.key === 'userids');

	// If it's an update, set the second field to null; otherwise, update the user field.
	form.value[1] = type === 'update' ? null : createFormField(userFieldConfig);
	if (form.value[1]) {
		form.value[1].value = formValues?.users;
	}

	// Update other fields and the divisionId.
	form.value[0].value = formValues?.name;
	form.value[2].value = formValues?.description;
	divisionId.value = formValues?.id;
};

const actions = [
	{
		key: 'update',
		icon: 'bi:pencil',
		tooltipText: 'Edit Divisi',
		btnClass: 'btn btn-sm text-primary-400',
	},
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
 * Watches the `current_page` property in the `meta` object of the store and updates the `currentPage` value accordingly.
 *
 * @param {Function} getter - A function that returns the value to be watched (e.g., `() => store?.meta?.current_page`).
 * @param {Function} callback - A callback function to be executed when the watched value changes.
 * @returns {void}
 */
watch(
	() => store?.meta?.current_page,
	(value) => {
		if (value) {
			currentPage.value = value;
			getDivisions();
		}
	}
);

watch(() => store?.navigate, (value) => {
	if (value !== null) {
		router.push(`/admin/project?division_id=${value?.id}`);
	}
});

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
			console.log("🚀 ~ value:", value)
			perPage.value = value;
			getDivisions();
		}
	}
);

const textModal = ref('');
/**
 * Handles the watch callback based on the value of the `typeAction` property.
 *
 * @param {Object} value - The value of the `typeAction` property in the store.
 * @param {string} value.key - The key indicating the type of action ('update' or 'add').
 * @param {Object} value.data - Additional data associated with the action.
 * @returns {void}
 */
const handleTypeAction = (value) => {
    if (value !== null) {
        if (value.key === 'update') {
            value.data.users = null;
			toogleModalForm('update');
		} else if (value.key === 'assign') {
			divisionId.value= value?.data?.id;
			isModalAssignationVisible.value = true;
		} else if (value?.key === 'name-table') {
			router.push(`/admin/project?division_id=${value?.data?.id}`);
		} else if (value?.key !== 'add') {
            toggleModalConfirm();
            textModal.value = `Apakah anda yakin ingin mengubah status menjadi ${value?.key} di divisi ${value.data.name}`;
        }

		setFormUpdate(value?.data, value?.key);
	}
};

/**
 * Watches the `typeAction` property in the store and performs actions based on its value.
 *
 * @param {Function} getter - A function that returns the value to be watched (e.g., `() => store?.typeAction`).
 * @param {Function} callback - A callback function to be executed when the watched value changes.
 * @returns {void}
 */
watch(
	() => store?.typeAction,
	(value) => {
		handleTypeAction(value);
	}
);

const getDivisions = () => {
	isLoading.value = true;
	const params = {
		paginate: perPage?.value,
		page: currentPage?.value,
		division_ids: divisionsIds?.value,
		entities: 'users.user.profile.medias',
	};
	
	const callback = (response) => {
		if (response?.data?.meta?.status) {
			isLoading.value = false;
			const divisions = response?.data?.data;
			const meta = response?.data?.meta;
			const divisionMap = divisions.map((division) => ({
				...division,
				assignto: division?.users
				?.filter((user) => user.type !== 'owner')
				.map((user) => ({
					id: user?.user?.id,
					url: user?.user?.profile?.medias?.url ?? userDummyImage,
					name: user?.user?.name,
				})),
			}));
			store.setMeta(meta);
			store.setData(divisionMap);
		}
	};

	const err = (e) => {
		console.log('e => ', e);
	};

	divisionApi.getData(params, callback, err);
};

const textBtnCreate = ref(null);
const checkCapabilities = () => {
	const checkIsSuperAdmin = roles?.value?.map(role => role?.role?.name).includes('superadmin');
	const checkIsAdmin = roles?.value?.map(role => role?.role?.name).includes('admin');
	const checkIsOnlyAdmin = !checkIsSuperAdmin && checkIsAdmin;
    const hasDivisions = user?.value?.divisions?.map(division => division?.devision_id) || [];
	textBtnCreate.value = 'Buat Divisi';
	if (checkIsOnlyAdmin && hasDivisions?.length === 0) {
		localStorage.removeItem('token');
		router.push('/login');
		toast?.error('Anda tidak memiliki divisi yang terdaftar, untuk lebih lanjut hubungi Admin');
	} else if (checkIsOnlyAdmin) {
		textBtnCreate.value = '';
		divisionsIds.value = hasDivisions;
	}
	getDivisions();
};

const submit = (value, type) => {
	if (type === 'add') {
		createDivision(value);
	} else {
		updateDivision(value);
	}
};

const createDivision = (value) => {
	const params = {
		name: value?.[0]?.value,
		usersIdsAssignTo: value?.[1]?.value?.map((curr) => curr.id),
		description: value?.[2]?.value,
	};
	const callback = (res) => {
		const data = res?.data?.data;
		store.insertData(data);
		close();
	};

	const err = (e) => {
		console.log(e);
	};
	divisionApi.createDivision(params, callback, err);
};

const updateDivision = (value) => {
	const params = {
		name: value?.[0]?.value,
		description: value?.[2].value,
        value,
	};
	hitEndpointUpdate(params);
};

const hitEndpointUpdate = (params, type = 'form') => {
    const callback = (res) => {
		if (res?.data?.meta?.status) {
			store.updateData(params?.value, divisionId.value, type);
			close();
		}
	};
	const err = (e) => {
        console.error(e);
    };
	divisionApi.updateDivision(divisionId?.value, params, callback, err);
};

const close = () => {
	isModalForm.value = false;
	isModalConfirm.value = false;
};

const isModalForm = ref(false);
const toogleModalForm = (type) => {
	typeForm.value = type;
	isModalForm.value = !isModalForm.value;
	if (type === 'add') {
        getDataUser();
		store.trigerAction({ key: type, data: form.value });
	}
};

const isModalConfirm = ref(false);
const toggleModalConfirm = (type) => {
	isModalConfirm.value = true;
};

const submitConfirm = (data, type) => {
    const params = {
        status: type,
        value: {
            status: type,
            data,
        }
    };
    hitEndpointUpdate(params, 'confirm');
};

const getDataUser = () => {
	const params = {
		paginate: 10,
		entities: 'profile.medias, roles',
		summary: 'user',
	};

	const callback = (response) => {
		if (response.data.meta.status) {
			const data = response.data.data;
			form.value[1].options = data.map((user) => ({
				id: user?.id,
				label: user?.name,
				image: user?.profile?.medias?.url ?? userDummyImage,
			}));
		}
	};

	const err = (e) => {
		console.log(e);
	};

	userApi.getAllUsers(params, callback, err);
};

const closeModalAssgn = () => {
	isModalAssignationVisible.value = false;
	getDivisions();
	
};

const init = () => {
	store?.setActions(actions);
	checkCapabilities();
};

onMounted(() => {
	store.setHeaders(headers);
	store.setNameConfig(null);
	init();
});
</script>

<style></style>
