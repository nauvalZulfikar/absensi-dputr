<template>
<div>
    <card :title="route.params.type">
        <div class="mt-4">
            <div class="text-md font-bold mb-2">Description</div>
            <TextAreaInput v-model="description" />
            <div class="my-3">
                <div class="my-2">
                    <span class="font-bold text-sm pb-2">
                        Upload Proof Of Work
                    </span>
                </div>
                <DropZoneVue :is-fetching="isFetching" @upload="uploadImageWorkOfProof" />
            </div>
            <vue-button 
                v-if="!clockInPreview"
                text="Lets Attendance" 
                btn-class="w-[100%] btn-dark"
                @click.prevent="startCamera($route.params.type)"
            />
        </div>
        <div v-if="clockInPreview" class="flex space-x-4">
            <div class="mb-4 flex-none">
                <div
                    class="mx-auto mt-6 rounded-md"
                >
                    <div class="text-md font-bold mb-2">Attandance Review</div>
                    <img
                        :src="clockInPreview"
                        class="object-cover h-full w-full block rounded-md"
                        width="300"
                    />
                </div>
            </div>
        </div>
        <div v-if="clockInPreview" class="mt-3 flex justify-end gap-2">
            <vue-button 
                text="Remove Attendance" 
                btn-class="btn btn-danger light rounded-lg"
                @click="removeAttendance"
            />
            <vue-button 
                text="Submit" 
                btn-class="btn btn-dark rounded-lg"
                @click.prevent="SubmitAttendance"
            />
        </div>
    </card>
    <ModalAttendance
        v-if="isOpenCamera"
        sizeClass="max-w-2xl"
        :active-modal="isOpenCamera"
        :type="route.params.type"
        @close="isOpenCamera = false"
        @upload="upload" 
    />
</div>
</template>

<script setup>
import Card from '@/components/Card';
import TextAreaInput from '@/components/Textarea';
import DropZoneVue from '@/components/Fileinput/DropZone.vue';
import { useRoute, useRouter } from 'vue-router';
import ModalAttendance from '@/components/Modal/Attandance.vue';
import VueButton from '@/components/Button/index.vue';
import { computed, onBeforeMount, onMounted, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { uploadMedia,uploadMediaImgur } from '@/helpers/media.js';
import { attendance } from '@/helpers/attendances.js';
import projectApi from '@/helpers/projects';
import axios from 'axios';

const toast = useToast();
const route = useRoute();
const router = useRouter();

const shiftId = ref();


const typeAction = computed(() => route.params.type);

const isOpenCamera = ref(false);
const startCamera = (type) => {
	isOpenCamera.value = true;
};

watch(() => isOpenCamera, (old, newValue) => {
})

const description = ref('');
const mediaWorkProofId = ref(null);
const attendances = ref(null);
const fullAddress = ref('');


const clockInPreview = ref(null);
const upload = (data) => {
    fullAddress.value = data?.fullAddress;
    const callback = (res) => {
        clockInPreview.value = res.data.data.url;
        attendances.value = {
            ...data,
            mediaAttendaceId: res.data.data.id
        };
    };
    const err = (e) => {
        console.log('e => ', e);
    };

    const formData = new FormData();
    formData.append('media', data.img);
    formData.append('type', 'attendance');
    uploadMedia(formData, callback, err);
};

const isFetching = ref(false);
const uploadImageWorkOfProof = (media) => {
    isFetching.value = true;
    const formData = new FormData();
    formData.append('media', media[0]);
    formData.append('type', 'workOfProof');
    
    const callback = (res) => {
        isFetching.value = false;
        mediaWorkProofId.value = res.data.data.id;
    };
    const err = (e) => {
        isFetching.value = true;
        console.log('e => ', e);
    };

    uploadMedia(formData, callback, err);


};

const removeAttendance = () => {
    clockInPreview.value = null;
    toast.success('Kehadiran Berhasil di hapus', {
        timeout: 2000,
    });
}

const checkValidRoute = () => {
    const validTypes = ['clockin', 'clockout'];
    const typeParam = route.params.type;
    if (!validTypes.includes(typeParam)) {
        router.replace('/error')
    }
};

const formattedCurrentTime = computed(() => {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');

    const timeString = `${hours}:${minutes}:${seconds}`;

    return timeString;
})

const getDetailProject = () => {
    const params = {
        entities: 'shift',
    }
    const callback = (response) => {
        const project = response?.data?.data;
        shiftId.value = project?.shift?.[0]?.shift_id ?? route?.query?.shift_id
        if (!shiftId?.value) {
            toast?.error('Project Ini Tidak Memiliki Shift, Untuk Lebih lanjut hubungi Admin')
            router?.push(`/detail-project/${project?.id}`);
        }
    }

    const err = (e) => {
        console.log(e);
    }
    
    projectApi.getDetailProject(route?.params?.project_id, params, callback, err)
};

onMounted(() => {
    getDetailProject();
    checkValidRoute();
});

const SubmitAttendance = () => {
    const form = {
        mediaAttendaceId: attendances.value.mediaAttendaceId,
        mediaOfWorkId: mediaWorkProofId.value,
        projectId: route.params.project_id,
        latitude: attendances.value.lat,
        longtitude: attendances.value.long,
        action: route.params.type,
        time: formattedCurrentTime.value,
        full_address: fullAddress.value,
        shiftId: route?.query?.shift_id,
    };

    const callback = (response) => {
        if (response.data.meta.status) {
            router.push(`/detail-project/${route.params.project_id}`);
            toast.success('Laporan Kehadiran Berhasil Di simpan', {
                timeout: 2000,
            }); 
        }
    };
    const err = (e) => {
        console.log(e);
    };

    attendance(form, callback, err);
};


</script>

<style>

</style>
