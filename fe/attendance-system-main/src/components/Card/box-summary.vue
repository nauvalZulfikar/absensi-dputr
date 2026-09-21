<template>
<div
    v-for="(item, i) in statistics"
    :key="i"
    :class="[{'bg-opacity-50': item?.key === route.query.summary}]"
    class="box-summary bg-primary-500"
    @click="setFilterSummary(item?.key)"
>
    <div
        class="mx-auto h-6 w-6 flex items-center justify-center rounded-full bg-white text-lg mb-2"
        :class="item.text">
        <Icon :icon="item.icon"
    />
    </div>
    <span
        class="block text-xs text-slate-600 font-medium dark:text-white"
        >{{ item.title }}</span
    >
    <span
        v-if="!isLoading"
        class="block text-sm text-slate-900 dark:text-white font-medium"
    >
        {{ item.count }}
    </span>
    <page-loader v-else customClass="mx-auto h-8 w-8" />
</div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import Icon from '@/components/Icon';
import pageLoader from '../Loader/pageLoader.vue';
import { ref } from "vue";


const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
// 
const props = defineProps({
    statistics: {
        type: Array,
        default: () => ([])
    }
});

const setFilterSummary = (key) => {
    router.push({...route, query: { ...route.query, summary: key }});
};
</script>

<style lang="scss">
.box-summary {
    @apply rounded-md p-2 bg-opacity-[0.15] dark:bg-opacity-50 text-center transition-all duration-300 hover:bg-opacity-50 cursor-pointer;
}
</style>
