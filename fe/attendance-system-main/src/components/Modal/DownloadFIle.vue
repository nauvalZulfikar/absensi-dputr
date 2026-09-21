<template>
    <modal 
        :active-modal="activeModal"
		:title="title"
		sizeClass="max-w-2xl max-h-2xl"
		@close="close"
    >
        <div>
            <div>
                <span class="text-sm font-bold">
                    Terdapat 2 Catatan Kehadiran yang Ditemukan.
                </span>
            </div>
            <div>
                <span class="text-sm">
                    Dari Tanggal: {{dayjs(since).format('dddd DD MMMM YYYY') ?? '-'}}
                </span>
            </div>
            <div>
                <span class="text-sm">
                    Sampai Tanggal: {{dayjs(until).format('dddd DD MMMM YYYY') ?? '-'}}
                </span>
            </div>

            <div v-if="fileName && exportLink" class="border rounded-md border-success-400 mt-3 p-2 flex justify-between">
                <div>
                    <span class="font-bold text-xs text-success-700">
                        File Berhasil di download: 
                    </span>
                </div>
                <div>
                    <vue-button 
                        :text="`Download File ${fileName}`" 
                        btn-class="btn p-0 btn-link-export"
                        @click="downloadFile(exportLink);"
                    />
                </div>
            </div>

            <div class="flex justify-between mt-3">
                <vue-button 
                    text="Lihat Riwayat Import" 
                    btn-class="btn btn-sm btn-outline-primary px-0"
                    @click="$router.push('/admin/file-manager')"
                />
            </div>
        </div>
        <template #footer>
            <vue-button text="Cancel" btn-class="btn btn-sm btn-outline-danger" @click="close"  />
            <vue-button text="Export" btn-class="btn btn-sm btn-success" :is-loading="isLoading" @click="exportData" />
        </template>
    </modal>
</template>
<script setup>
import Modal from '@/components/Modal';
import VueButton from '@/components/Button';
import dayjs from 'dayjs';
import { exportAttendance } from '@/helpers/attendances';
import { downloadFile } from '@/constant/helpers';
import { ref } from 'vue';

const emits = defineEmits(['close', 'submit']);
const isLoading = ref(false);
const props = defineProps({
	activeModal: {
		type: Boolean,
	},
	title: {
		type: String,
	},
	text: {
		type: String,
	},
    since: {
        type: String,
    },
    until: {
        type: String,
    },
});

const close = () => {
    emits('close');
};

const submit = () => {
    emits('submit');
}

const fileName = ref('');
const exportLink = ref('');

const exportData = () => {
    isLoading.value = true;
    const params = {
        since: props?.since,
        until: props?.until,
        admin_mode: true,
    };
    
    const callback = (res) => {
        const data = res.data?.data;
        exportLink.value = `${import.meta.env.VITE_AUTH_API_URL}/export-data/${data?.file?.file_name}`;
        fileName.value = data?.file?.file_name;
        isLoading.value = false;
    }

    const err = (e) => {
        console.warn(e);
    }
    
    exportAttendance(params, callback, err);
};


</script>

<style lang="scss">
    .btn-link-export {
        span {
            span {
                @apply text-xs text-primary-400;
            }
        }
    }
</style>
