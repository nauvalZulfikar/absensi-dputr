<template>
<header class="flex justify-between items-center">
    <div class="flex space-x-2 sm:space-x-3 items-center rtl:space-x-reverse">
        <div class="flex-none">
            <div
                class="h-10 w-10 rounded-md sm:text-lg bg-slate-100 text-slate-900 dark:bg-slate-600 dark:text-slate-200 flex flex-col items-center justify-center font-normal capitalize text-sm">
                {{
                    element?.name?.charAt(0) +
                    element?.name?.charAt(1)
                }}
            </div>
        </div>
        <div class="flex-1 font-medium sm:text-base text-sm leading-6">
            <router-link
                :content="element.name ?? 'name'"
                v-tippy="{
                    placement: 'top',
                }"
                :to="`/detail-project/${element?.id}?shift_id=${element?.shift_id}`"
                class="board-title dark:text-slate-200 text-slate-900 truncate hover:text-primary-600">
                {{ element?.name ?? 'name' }} - <span class="text-sm">{{ element?.shift?.type ?? 'Reguler' }}</span>
            </router-link>
        </div>
    </div>
</header>
</template>

<script setup>
const props = defineProps({
    element: {
        type: Object,
        default: () => ({}),
    }
})
</script>
