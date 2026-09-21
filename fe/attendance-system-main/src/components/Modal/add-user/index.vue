<template>
	<modal 
        :active-modal="activeModal"
    >
		<div>
			<vue-select
                @input="setUserSelected($event)"
            >
				<vSelect
					placeholder="Pilih Users"
					:options="userOptions"
                    multiple
                    v-model="userSelected"
                    @input="setUserSelected($event)"
                >
					<template #option="{ label, image, roles }">
						<div>
							<div class="flex items-center">
								<span
									v-if="image"
									class="w-10 h-10 rounded-full ltr:mr-4 rtl:ml-4 flex-none">
									<img
										v-if="image"
										:src="image"
										:alt="image"
										class="object-cover w-full h-full rounded-full" />
								</span>
								<div class="text-start">
									<span
										class="text-sm text-slate-600 dark:text-slate-300 capitalize text-star">
										{{ label }}
									</span>
								</div>
							</div>
						</div>
					</template>
				</vSelect>
			</vue-select>
		</div>

        <template #footer>
            <div class="flex justify-end mt-4 gap-2">
                <vue-button
                    text="Cancel"
                    btn-class="btn btn-danger light btn-sm"
                    @click="$emit('close')" 
                />
                <vue-button
					:is-loading="isLoading"
                    text="Confirm"
                    btn-class="btn btn-primary light btn-sm"
                    @click="submit" 
                />
            </div>
        </template>
	</modal>
</template>

<script setup>
import Modal from '@/components/Modal';
import VueSelect from '@/components/Select/VueSelect';
import vSelect from 'vue-select';
import VueButton from '@/components/Button';
import {ref,computed} from 'vue';
import { useUserStore } from '@/store/user';

const props = defineProps({
	activeModal: {
		type: Boolean,
		default: null,
	},
    users: {
        type: Array,
        default: []
    },
	isLoading: {
		type: Boolean,
		default: false,
	}
});

const userSelected = ref([]);

const storeUser = useUserStore();

const setUserSelected = (value) => {
    console.log('value -', value);
}

const emits = defineEmits('submit');

const userOptions = computed(() => storeUser?.userOptions);

const submit = () => {
    emits('submit', userSelected.value);
    userSelected.value = null;
};
</script>

<style lang="scss">
.fromGroup {
	ul {
		max-height: 100px !important;
		overflow: scroll !important;

		li {
            @apply bg-white;
			&:hover {
                @apply bg-primary-500;
                span {
                    @apply text-white;
                }
			}
		}
	}
}
</style>
