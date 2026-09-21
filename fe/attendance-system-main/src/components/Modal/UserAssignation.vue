<template>
<modal 
    :active-modal="activeModal"
    :title="title"
    sizeClass="max-w-full"
>
    <div>
        <ListRepeater 
            :users="users" 
            :divisionId="divisionId" 
            :projectId="projectId" 
        />
    </div>
    <template #footer>
        <div class="flex justify-end mt-4 gap-2">
            <vue-button
                text="Tutup"
                btn-class="btn btn-dark btn-sm"
                @click="$emit('close')" 
            />
        </div>
    </template>
</modal>
</template>
<script setup>
import Modal from '@/components/Modal';
import ListRepeater from '@/components/Repeater/List.vue';
import VueButton from '@/components/Button';
import { onMounted } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: () => '',
    },
    activeModal: {
        type: Boolean,
        default: false,
    },
    users: {
        type: Array,
        default: null,
    },
    divisionId: {
		type: [String, Number],
		default: null,
	},
    projectId: {
		type: [String, Number],
		default: null,
	},
});

onMounted(() => {   
    
});
</script>
