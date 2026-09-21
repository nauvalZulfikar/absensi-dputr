<template>
<ul class="breadcrumbs">
    <li class="text-primary-500">
        <router-link :to="{ name: 'Home' }" class="text-lg">
            <Icon :icon="icon" />
        </router-link>
        <span class="breadcrumbs-icon rtl:transform rtl:rotate-180">
            <Icon icon="heroicons:chevron-right" />
        </span>
    </li>
    <li 
        v-if="typeof groupParent === 'string' || typeof groupParent === 'object'" 
        class="text-primary-500"
    >
        <button type="button" class="capitalize">
            {{ groupParent }}
        </button>
        <span class="breadcrumbs-icon rtl:transform rtl:rotate-180">
            <Icon icon="heroicons:chevron-right" />
        </span>
    </li>
    <li class="capitalize text-slate-500 dark:text-slate-400">
        {{ sectionName }}
    </li>
</ul>
</template>

<script setup>
import Icon from "@/components/Icon";
defineProps({
    groupParent: {
        type: [String, Object, Array],
        default: '',
    },
    icon: {
        type: String,
        default: 'heroicons-outline:home',
    },
    sectionName: {
        type: String,
        default: '',
    },
});
</script>

<style>

</style>
