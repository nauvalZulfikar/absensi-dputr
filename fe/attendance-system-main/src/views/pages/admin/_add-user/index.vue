<template>
	<div>
		<data-table 
            :title="titlePage" 
            :is-loading="isLoading"
            btn-text="Tambah Anggota" 
            @open-modal-add="isModalUserVisible = true" 
        />

        <ModalAddUser 
            :active-modal="isModalUserVisible" 
            :users="userOptions"
            :isLoading="isLoading"
            @close="isModalUserVisible = false"
            @submit="submit"
        />

        <Confirm 
            :active-modal="isModalConfirmVisible" 
            :text="textConfirm"
            @close="isModalConfirmVisible = false"
            @submit="confirmDelete"
        />
	</div>
</template>

<script setup>
import DataTable from '@/components/DataTable/index.vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { ref, onMounted, computed, watch } from 'vue';
import userApi from '@/helpers/user';
import { useDataTableStore } from '@/store/data-table';
import { userDummyImage } from '@/constant/static';
import { useProjectStore } from '@/store/project';
import { useToast } from 'vue-toastification';
import ModalAddUser from '@/components/Modal/add-user';
import projectApi from '@/helpers/projects';
import Confirm from '@/components/Modal/Confirm';
import { addUserShift, deleteUserShift}  from '@/helpers/shift';
import { useUserStore } from '@/store/user';

const titlePage = ref('');
const router = useRouter();
const route = useRoute();
const toast = useToast();
const isModalUserVisible = ref(false);
const storeUser = useUserStore();
const isLoading = ref(false);


const store = useDataTableStore();
const storeProject = useProjectStore();

const initPage = () => {
    const routes = ['project', 'division', 'shift'];
    const validRoute = routes.some(curr => route?.params?.type === curr);
	if (validRoute && storeProject.selectedProject) {
		titlePage.value = ` Anggota ${route?.params?.type} ${storeProject.selectedProject?.name}`;
	} else if (!storeProject?.selectedProject) {
		router?.push('/admin/project');
	}
};

const projectId = computed(() => route?.params?.id);
const textConfirm = ref('');
const isModalConfirmVisible = ref(false);

const headers = [
	{
		label: 'Name',
		field: 'name',
	},
	{
		label: 'Roles',
		field: 'roles',
	},
	{
		label: 'Username',
		field: 'userName',
	},
	{
		label: 'Email',
		field: 'email',
	},
	{
		label: 'Status',
		field: 'status',
	},
	{
		label: 'Actions',
		field: 'actions',
	},
];

const actions = [
    {
        key: 'delete',
        icon: 'material-symbols:delete',
        tooltipText: 'Hapus Anggota',
        btnClass: 'btn btn-sm text-red-400',
    },
];

watch(() => store?.typeAction, (value) => {
    if (value.key === 'delete') {
        isModalConfirmVisible.value = true;
        textConfirm.value = `Apakah Anda yakin ingin menghapus Anggota ${value?.data?.name}`
    }
})

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
			getUsers();
		}
	}
);

 watch(
	() => store?.meta?.current_page,
	(value) => {
		if (value) {
			currentPage.value = value;
			getUsers();
		}
	}
);

const users = ref();
const getUsers = () => {
    isLoading.value = true;
	const params = {
        project_ids: [projectId.value],
        entities: 'profile.medias, roles.role, projects, shift',
		// not_have_this_projects:
        shift_id: route?.query?.shift_id,
        paginate: perPage?.value,
        page: currentPage?.value,
	};
    
	const callback = (res) => {
        if (res?.data?.meta?.status) {
            isLoading.value = false;
                users.value = res?.data?.data.map((user) => {
                return {
                    ...user,
                    image: user?.profile?.medias?.url ?? userDummyImage,
                    roles: user?.roles?.map((role) => role?.role?.name) ?? '-',
                    status: user?.status ?? '-',
                };
            });
            const meta = res?.data?.meta;
            store?.setMeta(meta);
            store?.setData(users.value);
        }
	};

	const err = (e) => {
		console.log(e);
	};

	userApi.getAllUsers(params, callback, err);
};

const userOptions = ref();

const fetchParams = computed(() => {
    let params = null;
    if (route?.params?.type === 'project') {
        params = {
            not_have_this_projects:[projectId.value],
        };
    } else if (route?.params?.type === 'shift') {
        params = {
            not_have_this_shift: [route?.query?.shift_id],
            project_ids: [projectId?.value],
            // not_have_this_projects:[projectId.value],
        }
    }

    return {
        ...params,
        entities: 'profile.medias, roles.role',
        roleIds: [2,4],
    }
});

const getUserSelected = () => {
	const params = fetchParams.value;

	const callback = (response) => {
		if (response?.data?.meta?.status) {
			const data = response?.data?.data;
			userOptions.value = data.map(curr => ({
                ...curr,
                label: curr?.name,
                id: curr?.id,
            }))
            .filter(curr => !users?.value?.some(user => user?.id === curr?.id));
            storeUser?.setUserOptions(userOptions.value);
		}
	};
	const err = (e) => {
		console.error(e);
	};

	userApi.getUserSelected(params, callback, err);
};

const submit = (users) => {
    if (route?.params?.type === 'project') {
        insertUserProject(users);
    } else if (route?.params?.type === 'shift') {
        insertUserShift(users);
    }
}

const insertUserProject = (users) => {
    isLoading.value = true;
	const params = {
        user_ids: users.map(curr => curr?.id),
		project_id: projectId?.value,
		// division_id: props?.divisionId,
	};
	const callback = (response) => {
        if (response?.data?.meta?.status) {
            isLoading.value = false;
            toast?.success('Berhasil Tambah Data');
            isModalUserVisible.value = false;
            getUsers();
            getUserSelected();
		}
	}
	const err = (e) => {
        console.log(e);
	};
    projectApi.insertUsersProject(params, callback, err);
};

const insertUserShift = (users) => {
    isLoading.value = true;
    const params = {
        user_ids: users?.map(curr => curr?.id),
        shift_id: route?.query?.shift_id,
        project_ids: [projectId?.value],
    };
    const callback = (res) => {
        if (res?.data?.data) {
            isLoading.value = false;
            toast?.success('Berhasil Tambah Data');
            isModalUserVisible.value = false;
            getUsers();
            getUserSelected();
        }
    };
    const err = (e) => {
        console.log(e);
    };
    
    addUserShift(params, callback, err);
};

const confirmDelete = (users) => {
    if (route?.params?.type === 'project') {
        deleteProjectUser(users);
    } else if (route?.params?.type === 'shift') {
        deleteShiftUser(users);
    }
};

const deleteProjectUser = (data) => {
    const index = data?.projects.findIndex(curr => parseInt(curr?.project_id) === parseInt(projectId.value));
    const relationId = data?.projects?.[index]?.id;
    const callback = (res) => {
        if (res?.data?.data) {
            isModalConfirmVisible.value = false;
            toast?.success('Berhasil mengahapus keanggotaan');
            getUserSelected();
            getUsers();
        }
    };

    const err = (e) => {
        console.log(e);
    };

    projectApi.deleteUserProject(relationId, callback, err);
};

const deleteShiftUser = (users) => {
    const index = users?.shift?.findIndex(curr => curr?.shift_id === parseInt(route?.query?.shift_id));
    const relationId = users?.shift?.[index]?.id;
    const params = {
        relation_id: relationId,
    };
    const callback = (res) => {
        if (res?.data?.data) {
            isModalConfirmVisible.value = false;
            toast?.success('Berhasil mengahapus keanggotaan');
            getUserSelected();
            getUsers();
        }
    };

    const err = (e) => {
        console.log(e);
    };

    deleteUserShift(params, callback, err);
};


const init = () => {
	initPage();
	getUsers();
    getUserSelected();
	store?.setHeaders(headers);
    store?.setActions(actions);
};

onMounted(() => {
	init();
});
</script>
