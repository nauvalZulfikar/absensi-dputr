<template>
<div>
    <card>
        <data-table 
            title="Akun"
            btn-text="Buat Akun"
            :is-loading="isLoading"
            @open-modal-add="toogleModalUser"
        />
    </card>
    <modal-form 
        :active-modal="isModalAddUser"
        :fields="form"
        btn-text="Buat Akun"
        :is-fetching="isFetching"
        @submit="submit"
        @close="close" 
    />
</div>
</template>

<script setup>
import Card from '@/components/Card/index.vue';
import DataTable from '@/components/DataTable/index.vue';
import ModalForm from '@/components/Modal/Form.vue';
import userApi from '@/helpers/user.js';
import { getRoles } from '@/helpers/roles.js';
import { onBeforeUnmount, onMounted, ref, watchEffect,watch, computed } from 'vue';
import { useDataTableStore } from '@/store/data-table.js';
import boxSummary from '@/components/Card/box-summary.vue';
import user from '@/helpers/user.js';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const userDummyImage = 'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';
const store = useDataTableStore();
const router = useRouter()
const route = useRoute()
const toast = useToast();

const dataUser = computed(() => JSON.parse(localStorage.getItem('users')));
const roles = computed(() => dataUser?.value?.roles);
const divisionsIds = ref();
const isLoading = ref(false);
// Define Headers
const headers = [
    {
        label: 'Name',
        field: 'name', 
    },
    {
        label: 'Roles',
        field: 'roles'
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
        field: 'status'
    },
    // {
    //     label: 'Actions',
    //     field: 'actions',
    // }
];

const summaries = ref([
    {
        key: 'all',
		title: 'Total Akun',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'active',
		title: 'Akun Aktif',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'not_active',
		title: 'Akun Tidak Active',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'superadmin',
		title: 'Superadmin',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'admin',
		title: 'Admin',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'user',
		title: 'User',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
    {
        key: 'supervisor',
		title: 'Supervisor',
		count: '0',
		bg: 'bg-info-500',
		text: 'text-info-500',
		percent: '25.67% ',
		icon: 'heroicons-outline:menu-alt-1',
	},
]);

const setNameConfig = ref({
    icons: [
        {
            icon: 'mdi:email-send',
            tooltipText: 'Kirim email untuk mengaktivasi akun ini',
            btnClass: 'btn-sm btn hover:text-success-600 p-0',
        },
        {
            icon: 'octicon:log-16',
            tooltipText: 'Lihat catatan Kehadiran',
            btnClass: 'btn-sm btn hover:text-success-600 p-0',
        }
    ],
});

const form = ref([
    {
        type: 'text',
        value: '',
        error: '',
        key: 'name',
        label: 'Nama',
        placeholder: 'Masukan Nama'
    },
    {
        type: 'text',
        value: '',
        error: '',
        key: 'username',
        label: 'Username',
        placeholder: 'Masukan Username'
    },
    {
        key: 'roleids',
        type: 'multiselect',
        value: null,
        error: '',
        multiple: true,
        options: [],
        label: 'Role',
        placeholder: 'Masukan Role',
    },
    {
        type: 'email',
        value: '',
        error: '',
        key: 'email',
        label: 'Email',
        placeholder: 'Masukan Email',
    },
]);


const users = ref([]);
const currentPage = ref(1);
const perPage = ref(10);
const getDataUser = () => {
    isLoading.value = true;
    const params = {
        entities: 'roles.role,profile.medias',
        paginate: perPage.value,
        page: currentPage?.value,
        summary: route?.query?.summary,
        division_ids: divisionsIds?.value?.map(division => division?.devision_id),
    };
    const callback = (response) => {
        if (response?.data?.meta?.status) {
            isLoading.value = false;
            const data = response.data.data;
            users.value = data.map(user => {
                return {
                    ...user,
                    image: user?.profile?.medias?.url ?? userDummyImage,
                    roles: user?.roles?.map(role => role?.role?.name) ?? '-',
                    status: user?.status ?? '-',
                }
            });
            store.setMeta(response.data.meta);
            store.setData(users.value);
        }
    };

    const err = (e) => {
        console.log(e);
    }

    userApi.getAllUsers(params, callback, err);
};

const checkUserCapabilities = () => {
	const checkIsSuperAdmin = roles?.value?.map(role => role?.role?.name).includes('superadmin');
	const checkIsAdmin = roles?.value?.map(role => role?.role?.name).includes('admin');
	const checkIsOnlyAdmin = !checkIsSuperAdmin && checkIsAdmin;
    const hasDivisions = dataUser?.value?.divisions?.map(division => division) || [];
	if (checkIsOnlyAdmin && hasDivisions?.length === 0) {
        localStorage.removeItem('token');
		router.push('/login');
		toast?.error('Anda tidak memiliki divisi yang terdaftar, untuk lebih lanjut hubungi Admin');
	} else if (checkIsOnlyAdmin) {
        divisionsIds.value = hasDivisions;
        form.value[3].options = hasDivisions?.map(curr => ({
            id: curr.devision_id,
            label: curr?.division?.name,
        }))
    }
	getDataUser();
};

const getUserSummary = () => {
    const params = {};
    const callback = (res) => {
        const data = res.data.data;
        summaries.value[0].count = data?.all;
        summaries.value[1].count = data?.active;
        summaries.value[2].count = data?.not_active;
        summaries.value[3].count = data?.superadmin;
        summaries.value[4].count = data?.admin;
        summaries.value[5].count = data?.user;
        summaries.value[6].count = data?.supervisor;
    }
    const err = () => {};
    userApi.getUserSummary(params, callback, err);
};

const getRolesData = () => {
    const callback = (res) => {
        let data = res?.data?.data;
        if (divisionsIds?.value?.length > 0) {
            data.splice(0, 1);
        }
        form.value[2].options = data.map(curr => {
            return {
                id: curr?.id,
                label: curr?.name
            }
        });
    };
    const err = (e) => {
        console.log(e);
    }
    getRoles({type: 'selected'}, callback, err);
}

watch(() => store?.meta?.current_page, (value) => {
    currentPage.value = value;
    getDataUser();
});

watch(() => store?.meta?.per_page, (value) => {
    perPage.value = value;
    getDataUser();
})

onMounted(() => {
    store.setHeaders(headers);
    store.setNameConfig(null);
    getRolesData();
    checkUserCapabilities();
});

const isModalAddUser = ref(false);
const toogleModalUser = () => {
    isModalAddUser.value = !isModalAddUser.value;
    store.setDisableSearch(true);
};

const close = () => {
    isModalAddUser.value = false;
    store.setDisableSearch(false);
};

const submit = (form, type) => {
    form.value = form;
    const value = form?.value.map(curr => curr.value);

    if (type === 'create') {
        createUser(value);
    } else if(type === 'update') {
        updateUser(value);
    }
};

const isFetching = ref(false);
const createUser = (value) => {
    isFetching.value = true;
    const params = {
        name: value[0],
        username: value[1],
        role_ids: value?.[2].map(curr => curr.id),
        email: value[3],
    };
    const callback = (res) => {
        if (res?.data?.meta?.status) {
            isFetching.value = false;
            const data = res?.data?.data;
            const user = {
                ...data,
                image: userDummyImage,
                roles: value?.[2]?.map(role => role?.label) ?? '-',
                username: data?.username ?? value?.[1],
            }
            store.insertData(user);
            toast.success('User berhasil di tambahkan lakukan aktivasi agar bisa di gunakan', {
                timeout: 2000,
            });
            isModalAddUser.value = false;
        }
    };

    const err = (e) => {
        console.log(e);
    };

    userApi.createUser(params, callback, err);
};

const updateUser = (value) => {};


</script>

<style>

</style>
