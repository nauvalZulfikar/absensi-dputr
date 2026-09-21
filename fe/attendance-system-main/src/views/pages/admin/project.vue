<template>
    <div>
        <card>
            <data-table title="Project" btn-text="Buat Project" :is-loading="isLoading"
                @open-modal-add="onOpenModalAdd" />
        </card>
        <ModalForm :active-modal="isModalFormVisible" title="Buat Project" :fields="form" :type="typeForm"
            @close="isModalFormVisible = false" @submit="onSubmit" />
        <ModalConfirm :active-modal="isModalConfirm" title="Publish Content" :text="textModal"
            @close="isModalConfirm = false" @submit="onSubmitStatus" />
        <ModalUserAssign :active-modal="isModalAssignation" :projectId="projectId" title="User Assignation"
            :users="usersAssignation" :divisionId="assignationUserDivisionId" @close="isModalAssignation = false" />
        <ShiftCreationModal :active-modal="isShiftModalVisible" :project-id="projectId"
            @close="isShiftModalVisible = false" @submit="createShift" />
        <ProductDetailModal :active-modal="isProductDetailModal" :data="project" title="Project Detail"
            @close="isProductDetailModal = false" @submit="onChangeProjectProcess" />
    </div>
</template>

<script setup>
import Card from '@/components/Card/index.vue';
import DataTable from '@/components/DataTable/index.vue';
import { createFormField, createOptionSelect, generateSlug } from '@/constant/helpers';
import division from '@/helpers/division';
import projectsApi from '@/helpers/projects.js';
import { useDataTableStore } from '@/store/data-table.js';
import { computed, onBeforeUnmount, onMounted, ref, watch, watchEffect } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ModalForm from '@/components/Modal/Form.vue';
import userApi from '@/helpers/user';
import divisionApi from '@/helpers/division';
import { useToast } from 'vue-toastification';
import ModalConfirm from '@/components/Modal/Confirm.vue';
import ModalUserAssign from '@/components/Modal/UserAssignation.vue';
import ShiftCreationModal from '@/components/Modal/ShiftCreation.vue';
import ProductDetailModal from '@/components/Modal/ProjectDetail.vue';
import { useProjectStore } from '@/store/project';
const userDummyImage =
    'https://static.vecteezy.com/system/resources/previews/018/765/757/original/user-profile-icon-in-flat-style-member-avatar-illustration-on-isolated-background-human-permission-sign-business-concept-vector.jpg';

const textModal = ref();
const store = useDataTableStore();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const user = computed(() => JSON.parse(localStorage.getItem('users')));
const roles = computed(() => user?.value?.roles);
const projectId = ref();
const isShiftModalVisible = ref(false);
const isProductDetailModal = ref(false);
const project = ref();
const isLoading = ref(false);

const storeProject = useProjectStore();

const headers = [
    {
        label: 'Project Number',
        field: 'projectNo',
    },
    {
        label: 'Name',
        field: 'name',
    },
    {
        label: 'Cost',
        field: 'cost',
    },
    { label: 'Mulai', field: 'startdate' },
    { label: 'Selesai', field: 'targetdate' },
    { label: 'Sisa Waktu', field: 'left_date' },
    { label: 'Fisik', field: 'physical_process' },
    { label: 'Divisi', field: 'division' },
    {
        label: 'Status',
        field: 'status'
    },
    { label: 'Anggota', field: 'assign' },
    {
        label: 'Actions',
        field: 'actions',
    },
];

const formConfig = [
    {
        type: 'text',
        key: 'name',
        label: 'Nama',
        placeholder: 'Masukan Nama',
    },
    {
        type: 'text',
        key: 'projectNo',
        label: 'Project No',
        placeholder: 'Masukan Nomor Project',
    },
    {
        type: 'text',
        key: 'cost',
        label: 'Nilai Project',
        placeholder: 'Masukan Nilai Project',
    },
    {
        key: 'divisionids',
        type: 'multiselect',
        value: null,
        options: [],
        multiple: false,
        label: 'Divisi',
        placeholder: 'Masukan Divisi',
    },
    {
        key: 'startdate',
        type: 'date',
        label: 'Masukan Tanggal Project Dimulai',
        placeholder: 'Masukan Tanggal Project Dimulai',
    },
    {
        key: 'targetdate',
        type: 'date',
        label: 'Masukan Tanggal Project Berakhir/selesai',
        placeholder: 'Masukan Tanggal Project Berakhir/selesai',
    },
    {
        key: 'description',
        type: 'textarea',
        label: 'Description',
        placeholder: 'Masukan Description',
    },
    {
        key: 'address',
        type: 'multiselect',
        options: [],
        error: '',
        label: 'Masukan Alamat Lengkap',
        placeholder: 'Masukan Address',
    },
];

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
    {
        key: 'add-users',
        icon: 'mdi:users-add-outline',
        tooltipText: 'Tambah Anggota Project',
        btnClass: 'btn btn-sm text-success-500',
    },
    {
        key: 'shift-creator',
        icon: 'fluent:shifts-add-24-regular',
        tooltipText: 'Tugaskan Shift',
        btnClass: 'btn btn-sm text-success-400',
    },
];

const form = ref(formConfig.map(createFormField));

watch(
    () => store?.typeAction,
    (value) => {
        if (value && value?.name === 'Project') {
            handleTypeAction(value);
        }
    }
);

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
        if (value && route?.path.includes('project')) {
            perPage.value = value;
            getDataProjects();
        }
    }
);

watch(
    () => store?.meta?.current_page,
    (value) => {
        if (value && route?.path.includes('project')) {
            currentPage.value = value;
            getDataProjects();
        }
    }
);

const isModalAssignation = ref(false);
const assignationUserDivisionId = ref();
const usersAssignation = ref([]);
const handleTypeAction = (value) => {
    if (value.key === 'update') {
        isModalFormVisible.value = true;
        typeForm.value = value.key;
        projectId.value = value?.data?.id;
        divisionsIds.value = [value?.data?.division?.id];
        setFormData(value);
    } else if (value.key === 'assign') {
        // isModalAssignation.value = true;
        router?.push(`/admin/project/${value?.data?.id}/${generateSlug(value?.data?.name)}`);
        storeProject?.setSelectedProject(value?.data);
        usersAssignation.value = value?.data?.users;
        projectId.value = value?.data?.id;
        assignationUserDivisionId.value = value?.data?.devisionId;
    } else if (value?.key === 'name-table') {
        project.value = value?.data;
        isProductDetailModal.value = true;
        projectId.value = value?.data?.id;
    } else if (value?.key === 'shift-creator') {
        isShiftModalVisible.value = true;
        projectId.value = value?.data?.id;
        storeProject?.setSelectedProject(value?.data);
    } else if (value?.key === 'add-users') {
        router?.push(`/admin/project/${value?.data?.id}/${generateSlug(value?.data?.name)}`);
    }
    else if (value?.key !== 'add') {
        toggleModalConfirm();
        textModal.value = `Apakah anda yakin ingin mengubah status menjadi ${value?.key} di project ${value.data.name}`;
    }
};

const isModalConfirm = ref(false);
const toggleModalConfirm = (type) => {
    isModalConfirm.value = true;
}

const setFormData = (value) => {
    form.value[0].value = value?.data?.name;
    form.value[1].value = value?.data?.projectNo;
    form.value[2].value = value?.data?.cost?.toString();
    form.value[3].value = createOptionSelect(value?.data?.division?.id, value?.data?.division?.name, null);
    form.value[4].value = value?.data?.startdate;
    form.value[5].value = value?.data?.targetdate;
    form.value[6].value = value?.data?.description;
    form.value[7].value = value?.data?.address;
};

const divisionId = ref(null);
const fetchParams = computed(() => {
    return {
        division_id: route?.query?.divison_id,
    };
});

const isModalFormVisible = ref(false);
const typeForm = ref('add');
const onOpenModalAdd = () => {
    isModalFormVisible.value = true;
    typeForm.value = 'add';
    form?.value?.forEach(element => {
        element.value = null;
    });
};

const getDataProjects = () => {
    isLoading.value = true;
    const params = {
        paginate: perPage?.value,
        page: currentPage?.value,
        division_id: divisionId?.value ?? route?.query?.division_id,
        division_ids: divisionsIds?.value ?? null,
        entities: 'users.user.profile.medias,users.user.roles.role,division,shift.shift',
    };
    const callback = (response) => {
        const meta = response?.data?.meta;
        const projects = response?.data?.data;
        isLoading.value = false;
        const totalDate = (start, end) => {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diffDays = endDate.getDate() - startDate.getDate();
            return diffDays;
        };
        const projectMap = projects.map(project => ({
            ...project,
            division: project?.division?.name,
            assignto: project?.users
                ?.filter((user) => user.type !== 'owner')
                .map((user) => ({
                    id: user?.user?.id,
                    url: user?.user?.profile?.medias?.url ?? userDummyImage,
                    name: user?.user?.name,
                })),
            left_date: `${totalDate(project?.startdate, project?.targetdate)} Lagi`,
            shift: project?.shift,
            physical_process: `${project?.physical_process ?? 0}%`,
        }))
        store.setData(projectMap);
        store?.setMeta(meta);
    };

    const err = (e) => {
        console.warn('e => ', e);
    };

    projectsApi.getDataProjects(params, callback, err);
};
const divisionsIds = ref();
const checkProjectCapabilities = () => {
    const checkIsSuperAdmin = roles?.value
        ?.map((role) => role?.role?.name)
        .includes('superadmin');
    const checkIsAdmin = roles?.value
        ?.map((role) => role?.role?.name)
        .includes('admin');
    const checkIsOnlyAdmin = !checkIsSuperAdmin && checkIsAdmin;
    const hasDivisions = user?.value?.divisions || [];
    if (checkIsOnlyAdmin && hasDivisions?.length === 0) {
        localStorage.removeItem('token');
        router.push('/login');
        toast?.error(
            'Anda tidak memiliki divisi yang terdaftar, untuk lebih lanjut hubungi Admin'
        );
    } else if (checkIsOnlyAdmin) {
        divisionsIds.value = hasDivisions?.map((division) => division?.devision_id);
    }
    getDataProjects();
    getSelectedDivisions();
};
const getSelectedDivisions = () => {
    const params = {
        typeGet: 'selected',
        division_ids: divisionsIds?.value,
        paginate: 100,
    };
    const callback = (res) => {
        const divisions = res?.data?.data;
        form.value[3].options = divisions.map(curr => ({
            label: curr?.name,
            id: curr?.id,
        }))
    }

    const err = (e) => {
        console.error(e);
    }

    divisionApi.getData(params, callback, err);
};

const init = () => {
    const query = route.query;
    divisionId.value = query?.division_id;
};

onMounted(() => {
    checkProjectCapabilities();
    init();
    store.setHeaders(headers);
    store?.setActions(actions);
    store?.setNameConfig(null)
});


const dataLong = ref(null);
const dataLat = ref(null);

const onInputSelectAddress = (address) => {
    generateLatAndLong(address);
};

const onSubmit = (data, type) => {
    const params = {
        name: data?.[0]?.value,
        projectNo: data?.[1]?.value,
        cost: data?.[2]?.value,
        devisionId: data?.[3].value?.id,
        startdate: data?.[4].value,
        targetdate: data?.[5].value,
        description: data?.[6].value,
        address: data?.[7].value?.label,
        longitude: data?.[7]?.value?.longitude,
        latitude: data?.[7]?.value?.latitude,
    };

    if (type === 'add') {
        createProject(params);
    } else if (type === 'update') {
        updateProject(data, params);
    }
};

const onSubmitStatus = (value, type) => {
    projectId.value = value?.id;
    const params = {
        status: type,
    }

    updateProject(params, params, 'confirm');
};

const createProject = (params) => {
    const callback = (response) => {
        if (response?.data?.meta.status) {
            const data = response?.data?.data;
            store?.insertData(data);
            toast?.success('Berhasil Membuat Project');
            isModalFormVisible.value = false;
        }
    }
    const err = (e) => {
        console.log(e);
    };
    projectsApi.createProject(params, callback, err);
};

const updateProject = (formValue, params, type = 'form') => {
    const callback = (response) => {
        if (response?.data?.meta?.status) {
            isModalFormVisible.value = false;
            isModalConfirm.value = false;
            isShiftModalVisible.value = false
            toast.success('Data Berhasil Di Update');
            store?.updateData(formValue, projectId?.value, type);
        }
    };
    const err = (e) => {
        console.log(e);
    }

    projectsApi.updateProject(projectId?.value, params, callback, err);
};

const createShift = (params) => {
    projectId.value = params?.id;
    updateProject(params, params, '');
};

const onChangeProjectProcess = (physical_process, disbursement_of_funds) => {
    const params = {
        physical_process,
        disbursement_of_funds,
    }

    updateProject(params, params, 'confirm');
};
</script>

<style></style>
