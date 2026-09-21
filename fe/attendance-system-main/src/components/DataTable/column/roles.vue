<template>
    <div class="flex flex-wrap">
    <div v-for="(role, index) in data.roles" :key="index" class="flex justify-start items-center p-2">
        <!-- <vue-badge :label="role" /> -->
        <span class="text-sm">
            {{ setNameRoles(role) }}
        </span>
    </div>
</div>

</template>

<script setup>
import {setNameRoles} from '@/constant/helpers';
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
</script>

<style>

</style>
