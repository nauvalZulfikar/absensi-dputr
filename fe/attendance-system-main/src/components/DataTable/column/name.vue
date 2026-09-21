<template>
<div class="flex items-center name-column">
    <span
        class="w-10 h-10 rounded-full ltr:mr-4 rtl:ml-4 flex-none">
        <img
            v-if="data && data.image"
            :src="data.image"
            :alt="data.image"
            class="object-cover w-full h-full rounded-full" 
        />
    </span>
    <div class="text-start">
        <span
            class="text-sm text-slate-600 dark:text-slate-300 capitalize text-start hover:text-primary-600 cursor-pointer"
            @click="nameAction"
        >
            {{ data.name }}
        </span>
        <!-- <div v-if="data && data?.roles && $route?.path?.includes('attendance')" class="grid grid-cols-1 gap-2">
            <vue-badge v-for="(role, index) in data?.roles" :key="index" :label="role?.role?.name" badge-class="text-sm" />
        </div> -->
        <div v-if="config?.icons.length > 0" class="grid grid-cols-4 mt-2 button-icon">
            <vue-button 
                v-for="(icon, index) in config?.icons"
                :key="index" 
                :icon="icon.icon" 
                :btn-class="icon.btnClass" 
                :icon-tooltip="icon.tooltipText"
                @click="handleButtonClick(icon)"
            />
        </div>
    </div>
</div>

</template>

<script setup>
import VueButton from '@/components/Button';
import VueBadge from '@/components/Badge';
import { useDataTableStore } from "@/store/data-table.js";
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps({
    /**
     * Type of button that can be received as a prop.
     * @type {String}
     * @default ''
     */
    type: {
        type: String,
        default: '',
    },
    /**
     * Data that can be received as a prop.
     * @type {Object}
     * @default {}
     */
    data: {
        type: Object,
        default: {},
    },
});

// Using the store from data-table.js
const store = useDataTableStore();

// Using a computed property to get configuration from the store
const config = computed(() => store?.nameConfig);
const route = useRoute();

const emit = defineEmits(['name-action'])

const nameAction = () => {
    const params = {
		key: 'name-table',
		data: props?.data,
        name: route?.name,
	};
	console.log("🚀 ~ nameAction ~ params.route?.name:", params.route?.name)
	store?.trigerAction(params);
};

/**
 * Function called when the button is clicked.
 * @returns {void}
 */
const handleButtonClick = (icon) => {
    const params = {
		key: icon,
		data: props?.data,
        name: route?.name,
	};
	console.log("🚀 ~ handleButtonClick ~ params.route?.name:", params.route?.name)
	store?.trigerAction(params);
};
</script>

<style lang="scss">
.name-column {
    .button-icon {
        opacity: 0;
    }

    &:hover {
        .button-icon {
            opacity: 1;
        }
    }
}
</style>
