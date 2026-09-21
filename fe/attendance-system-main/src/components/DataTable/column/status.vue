<template>
	<span class="block w-full">
		<span
			class="inline-block px-3 min-w-[90px] text-center mx-auto py-1 rounded-[999px] bg-opacity-25 text-sm"
			:class="[getColorStatus]"
        >
			{{ status }}
		</span>
	</span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: '',
    },
    data: {
        type: Object,
        default: {},
    },
});

const status = computed(() => props?.data?.status?.split('_').join(' ') ?? 'draft');

const getColorStatus = computed(() => {
    let color = 'text-success-500 bg-success-500';
    if (status.value === 'not active' || status.value === 'draft' || status.value === 'late') {
        color = 'text-warning-500 bg-warning-500'
    }
    return color;
});
</script>
