<template>
    <div class="flex justify-start -space-x-1.5">
        <div
            v-for="(user, userIndex) in data.assignto"
            :key="userIndex"
            class="h-6 w-6 rounded-full ring-1 ring-slate-100 cursor-pointer"
            @click="openModal"
        >
            <img
                :src="user?.url"
                :content="user?.name"
                v-tippy="{
                    placement: 'top'
                }"
                :alt="user.title"
                class="w-full h-full rounded-full" 
            />
        </div>
        <div
            class="assign-btn"
            v-tippy="{
                placement: 'top',
            }"
            :content="`Lihat ${data?.assignto?.length} Anggota`"
            @click="openModal"
        >
            {{ data?.assignto?.length > 0 ? `+${data?.assignto?.length ?? 0}` : '-'  }}
        </div>
    </div>
</template>

<script>
import {useDataTableStore} from '@/store/data-table';
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

    setup(props) {
        const route = useRoute();
        const store = useDataTableStore();

        const openModal = () => {
            const params = {
                data: props?.data,
                key: 'assign',
                name: route?.name,
            }
            console.log("🚀 ~ openModal ~ params.route?.name:", params.route?.name)
            store?.trigerAction(params)
        }

        return {
            openModal,
        }
    },
}
</script>

<style lang="scss">
    .assign-btn {
        @apply bg-white cursor-pointer dark:bg-slate-800 text-slate-900 dark:text-slate-300 text-xs ring-2 ring-slate-100 dark:ring-slate-700 rounded-full h-6 w-6 flex flex-col justify-center items-center;
    }
</style>
