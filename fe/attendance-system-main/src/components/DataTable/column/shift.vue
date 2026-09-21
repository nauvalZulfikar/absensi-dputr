<template>
    <div class="flex justify-start -space-x-1.5">
        <div
            class="rounded-full ring-1 ring-slate-100 cursor-pointer h-full"
            @click="openModal"
        >
            <vue-badge label="Lihat Shiuft" />
        </div>
    </div>
</template>

<script>
import {useDataTableStore} from '@/store/data-table';
import VueBadge from '@/components/Badge';
import { computed } from 'vue';
import { useRoute } from 'vue-router';
export default {
    props: {
        /**
         * Data that can be received as a prop.
         * @type {Object}
         * @default {}
         */
        data: {
            type: Object,
            default: {},
        }
    },

    components: {
        VueBadge,
    },

    setup(props) {
        const store = useDataTableStore();
        const route = useRoute();

        const openModal = () => {
            const shifts = props?.data?.shift;
            const params = {
                data: props?.data,
                key: 'shift-creator',
                shifts,
                name: route?.name,
            }
            console.log("🚀 ~ openModal ~ params.route?.name:", params.route?.name)
            store?.trigerAction(params)
        };

        const setDisplayShift = computed(() => {
            const shifts = props?.data?.shift;
            let displayStr = 'Lihat SHift';
            if (shifts?.length === 0) {
                displayStr = 'TIdak ada Shift';
            }

            return displayStr;
        });

        return {
            openModal,
            setDisplayShift,
        }
    },
}
</script>

<style lang="scss">
    .assign-btn {
        @apply bg-white cursor-pointer dark:bg-slate-800 text-slate-900 dark:text-slate-300 text-xs ring-2 ring-slate-100 dark:ring-slate-700 rounded-full h-6 w-6 flex flex-col justify-center items-center;
    }
</style>
