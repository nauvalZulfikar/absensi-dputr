<template>
    <modal
        :active-modal="activeModal"
        :title="title"
        sizeClass="max-w-full"
        @close="close"
    >
        <div>
            <div class="modal-content bg-white p-4">
                <div class="grid grid-cols-12">
                    <div class="col-span-8 border-r">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">{{ data?.name }}</h2>
                            <p class="text-gray-600">{{ data?.description }}</p>
                        </div>
                        <hr class="mt-6">
                        <div>
                            <h3 class="text-lg font-bold mb-2">Jam Masuk dan Keluar</h3>
                            <div>
                                <span>
                                    Waktu Masuk : Pukul {{ data?.timeIn }} WIB
                                </span>
                            </div>
                            <div>
                                <span>
                                    Waktu Keluar : Pukul {{ data?.timeOut }} WIB
                                </span>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div>
                            <h3 class="text-lg font-bold mb-2">Anggota Project</h3>
                            <div>
                                <span>
                                    Jumlah Anggota {{ data?.users?.length }} Orang
                                </span>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="">
                            <h3 class="text-lg font-bold mb-2">Fisik dan Pencairan</h3>
                            <div class="px-2">
                                <input-text
                                    v-model="physical_process"
                                    placeholder="Fisik (%)" 
                                />
                            </div>
                            <div class="px-2 mt-2">
                                <input-text v-model="disbursement_of_funds" placeholder="Pencairan (%)" />
                            </div>
                            <div class="flex justify-end p-2">
                                <vue-button text="Submit" btn-class="btn btn-sm btn-primary" @click="submit" />
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4 p-4">
                        <h3 class="text-lg font-bold mb-2">Users</h3>
                        <div v-if="data?.users.length > 0" class="overflow-y-scroll">
                            <div v-for="(user, index) in data?.users" :key="index" class="p-4 border-b mb-2 flex items-center">
                                <div class="mr-4">
                                    <img :src="user?.user?.profile?.medias?.url ?? userDummyImage" width="60" alt="User Image" class="rounded-full">
                                </div>
                                <div class="">
                                    <div>{{ user?.user?.name }}</div>
                                    <div class="flex justify-between">
                                        {{ user?.user?.email }} <vue-badge :label="user?.type" badge-class="bg-white text-primary-500 border light border-primary-500 ml-2" /> 
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex justify-center">
                            <span>
                                Tidak ada Anggota
                            </span>
                        </div>
                    </div>

                    <!-- Add additional details on the right side if needed -->
                </div>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end mt-4 gap-2">
                <vue-button
                    text="Tutup"
                    btn-class="btn btn-danger light btn-dark"
                    @click="$emit('close')" 
                />
            </div>
        </template>
    </modal>
</template>

<script setup>
import Modal from '@/components/Modal/index.vue';
import VueButton from '@/components/Button';
import { useDataTableStore } from '@/store/data-table';
import InputText from '@/components/Textinput';
import { computed, onMounted, ref, watch } from 'vue';
import dayjs from 'dayjs';
import { userDummyImage } from '@/constant/static';
import VueBadge from '@/components/Badge'
import { duplicateVar } from '@/constant/helpers';


const store = useDataTableStore();

const props = defineProps({
    activeModal: Boolean,
    title: String,
    text: String,
    data: Object
});

const physical_process = ref('');
const disbursement_of_funds = ref();

const emits = defineEmits(['close', 'submit']);

const close = () => {
    emits('close');
};

const init = () => {
    const data = duplicateVar(props?.data);
    physical_process.value = data?.physical_process;
    disbursement_of_funds.value = data?.disbursement_of_funds;
};

const formatShiftTime = (time) => {
    return dayjs(time).format('HH:mm');
};

const submit = () => {
    emits('submit', physical_process.value, disbursement_of_funds.value);
};

onMounted(() => {
    init();
});

watch(() => props?.data, () => {
    init();
})
</script>
