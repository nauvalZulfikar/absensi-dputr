<template>
	<div :class="parentClass">
		<div class="accordion-wrapper">
			<div
                class="flex justify-between cursor-pointer transition duration-150 font-medium w-full text-start text-base text-slate-600 dark:text-slate-300 px-0 py-4"
				:class="[updateAccordionStyle]"
                @click="toggleAccordionVisibility"
            >
                {{ title }}

                <span
                    class="text-slate-900 dark:text-white text-[22px] transition-all duration-300 h-5"
                    :class="[updateAccordionStyle('icon-chevron-side')]"
                >
                    <Icon icon="heroicons-outline:chevron-down" />
                </span>
            </div>
		</div>
        <Transition
            enter-active-class="submenu_enter-active"
            leave-active-class="submenu_leave-active"
            @before-enter="beforeEnter"
            @enter="enter"
            @after-enter="afterEnter"
            @before-leave="beforeLeave"
            @leave="leave"
            @after-leave="afterLeave"
        >
            <div
                v-if="isVisible"
                class="text-sm text-slate-600 font-normal bg-white dark:bg-slate-900 dark:text-slate-300 rounded-b-md"
                :class="[updateAccordionStyle]"
            >
                <slot class="px-0 py-2" name="content">
                    Content
                </slot>
            </div>
        </Transition>
	</div>
</template>

<script setup>
import { ref } from "vue";
import Icon from '@/components/Icon'

const props = defineProps({
	parentClass: {
		tyepe: String,
		default: '',
	},
	title: {
		tyepe: String,
		default: '',
	},
    isOpen: {
        type: Boolean,
        default: false,
    }
});

const isVisible = ref(props.isOpen);

const toggleAccordionVisibility = () => {
    isVisible.value = !isVisible.value;
};

const updateAccordionStyle = (type) => {
    let visibleStyle = 'bg-slate-50 dark:bg-slate-700 dark:bg-opacity-60 rounded-t-md';
    let closeStyle = 'bg-white dark:bg-slate-700 rounded-md';

    switch (type) {
        case 'icon-chevron-side':
            visibleStyle = 'rotate-180 transform';
            closeStyle = '';
            break;
        case 'content-section':
            visibleStyle = 'dark:border dark:border-slate-700 dark:border-t-0';
            closeStyle = 'l';
            break;
    }

    return isVisible.value ? visibleStyle : closeStyle;
}

const beforeEnter = (element) => {
    requestAnimationFrame(() => {
        if (!element.style.height) {
            element.style.height = "0px";
        }

        element.style.display = null;
    });
};

const enter = (element) => {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            element.style.height = `${element.scrollHeight}px`;
        });
    });
}

const afterEnter = (element) => {
    element.style.height = null;
};

const beforeLeave = (element) => {
    requestAnimationFrame(() => {
        if (!element.style.height) {
            element.style.height = `${element.offsetHeight}px`;
        }
    });
};

const leave = (element) => {
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            element.style.height = "0px";
        });
    });
}

const afterLeave = (element) => {
    element.style.height = null;
}
</script>

<style lang="scss" scoped>
.accordion-wrapper {
    @apply rounded-md;
}
</style>
