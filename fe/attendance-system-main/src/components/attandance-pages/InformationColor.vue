<template>
<div class="flex">
    <div class="border w-[20px] h-[20px] rounded-full" :class="[setBgInformation(colorCode)]" />
    <span class="font-bold text-sm ml-2">
        {{ label }}
    </span>
</div>
</template>

<script setup>
defineProps({
    colorCode: {
        type: String,
        default: '#0000',
    },
    label: {
        type: String,
        default: 'Progress',
    }
});

const setBgInformation = (colorCode) => `bg-[${colorCode}]`;
</script>

<style>

</style>
