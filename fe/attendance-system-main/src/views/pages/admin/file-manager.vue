<template>
    <div>
        <card>
            <data-table title="File Manager" :is-loading="isLoading" />
        </card>
    </div>
</template>


<script setup>
import Card from '@/components/Card';
import DataTable from '@/components/DataTable';
import { useDataTableStore } from '@/store/data-table';
import { onMounted, ref, watch, computed } from 'vue';
import { getFiles } from '@/helpers/files'
import dayjs from 'dayjs';
import { downloadFile } from '@/constant/helpers';


const headers = [
    { label: 'File Name', field: 'file_name' },
	{ label: 'Type', field: 'type' },
	{ label: 'Created', field: 'created_at' },
    {
        label: 'Actions',
        field: 'actions',
    }
];

const actions = [
    {
        key: 'download',
		icon: 'el:download',
		tooltipText: 'Unduh ulang data',
		btnClass: 'btn btn-sm text-primary-400',
	},
];

const store = useDataTableStore();
const perPage = ref(10);
const isLoading = ref(false);

watch(() => store?.typeAction, (value) => {
    if (value) {
        const exportLink = ref(`${import.meta.env.VITE_AUTH_API_URL}/export-data/${value?.data?.file_name}`);
        downloadFile(exportLink?.value);
        console.log("🚀 ~ watch ~ exportLink:", exportLink)
    }
});

watch(
    () => store?.meta?.per_page,
	(value) => {
        if (value) {
            perPage.value = value;
			getData();
		}
	}
);

const getData = () => {
    isLoading.value = true;
    const params = {
        paginate: perPage?.value,
    };
    const callback = (res) => {
        isLoading.value = false;
		const dateFormat = ' dddd,DD MMMM YYYYY HH:mm:ss';
        const data = res?.data?.data?.map(curr => ({
            file_name: curr?.file_name,
            type: curr?.type ?? 'Catatan Kehadiran',
            created_at: dayjs(curr?.created_at).format(dateFormat),
        }));
        store.setData(data);
        store?.setMeta(res?.data?.meta);
    }

    const err = (e) => {
        console.warn(e);
    };

    getFiles(params, callback, err);
};

onMounted(() => {
    store?.setHeaders(headers);
    store?.setActions(actions);
    getData();
})
</script>
