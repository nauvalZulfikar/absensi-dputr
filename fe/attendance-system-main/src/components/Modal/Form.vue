<template>
	<modal
		:active-modal="activeModal"
		:title="title"
		sizeClass="max-w-2xl max-h-2xl"
        class="text-primary-400"
		@close="$emit('close')">
		<div class="grid grid-cols-1 h-auto">
			<div v-for="(field, index) in forms" :key="index" class="mt-2">
                <!-- Text -->
				<text-input-field
					v-if="field?.type === 'text'"
					v-model="forms[index].value"
					:error="field.error"
					:type="field.type"
					:label="field.label"
					:placeholder="field.placeholder"
					@input="onInput($event, field)" 
                />
                <!-- TextArea -->
				<text-area-field
					v-if="field?.type === 'textarea'"
					v-model="forms[index].value"
					:error="field.error"
					:type="field.type"
					:label="field.label"
					:placeholder="field.placeholder"
					@input="onInput($event, field)" 
                />
                <!-- Email -->
				<text-input-field
					v-if="field?.type === 'email'"
					v-model="forms[index].value"
					:type="field.type"
					:error="field.error"
					:label="field.label"
					:placeholder="field.placeholder"
					@input="onInput($event, field)" 
                />
                <!-- PASSWORD -->
				<text-input-field
					v-if="field?.type === 'password'"
					v-model="forms[index].value"
					:type="field.type"
					:error="field.error"
					:label="field.label"
					:placeholder="field.placeholder"
					@input="onInput($event, field)" 
                />
                <!-- DATE -->
				<FormGroup
					v-if="field.type === 'date'"
					:label="field?.label"
					name="d1"
                >
					<flat-pickr
                        v-model="field.value"
						class="form-control"
						id="d1"
						placeholder="yyyy, dd M" 
                    />
				</FormGroup>
                <!-- Vue Select -->
				<vue-select
					v-if="field.type === 'multiselect'"
					:error="field.error"
					:label="field.label"
                    @input="onInputSelect($event, field)"
                >
					<vSelect
						:placeholder="field.placeholder"
						:options="field.options"
						v-model="forms[index].value"
						:multiple="field?.multiple"
                    >
						<template #option="{ label, image, roles }">
                            <div>
                                <div v-if="!isLoading" class="flex items-center">
								<span
									v-if="image"
									class="w-10 h-10 rounded-full ltr:mr-4 rtl:ml-4 flex-none">
									<img
										v-if="image"
										:src="image"
										:alt="image"
										class="object-cover w-full h-full rounded-full" 
                                    />
								</span>
								<div class="text-start">
                                    <span class="text-sm text-slate-600 dark:text-slate-300 capitalize text-star">
                                        {{ label }}<template v-if="roles"> -  {{ roles.join(' - ') }}</template>
                                    </span>
								</div>
							</div>
                            <page-loader v-else />

                            </div>
						</template>
					</vSelect>
				</vue-select>
			</div>
			<div class="flex justify-end mt-4 gap-2">
				<vue-button
					text="Cancel"
					btn-class="btn btn-danger light btn-sm"
					@click="$emit('close')" />
				<vue-button
					text="Submit"
					:is-disabled="!noErrors"
					btn-class="btn btn-primary light btn-sm"
					:is-loading="isFetching"
					@click="submit" />
			</div>
		</div>
	</modal>
</template>

<script setup>
import VueButton from '@/components/Button';
import TextInputField from '@/components/Textinput/index.vue';
import Modal from '@/components/Modal/index.vue';
import { computed, ref, watchEffect } from 'vue';
import { duplicateVar } from '@/constant/helpers';
import TextAreaField from '@/components/Textarea';
import FormGroup from '@/components/FromGroup';
import pageLoader from '@/components/Loader/pageLoader.vue';
import VueAlert from '@/components/Alert';

import VueSelect from '@/components/Select/VueSelect';
import vSelect from 'vue-select';

import * as yup from 'yup';


// Emit definition
const emit = defineEmits(['submit', 'on-input-select']);

// Props
const props = defineProps({
	activeModal: {
		type: Boolean,
		default: false,
	},
	fields: {
		type: Array,
		default: () => [
			{
				type: 'text',
				key: 'name',
				label: 'Nama',
				placeholder: 'Masukan Nama',
			},
		],
	},
	type: {
		type: String,
		default: 'create',
	},
	title: {
		type: String,
		default: null,
	},
	isFetching: {
		type: Boolean,
		default: false
	}
});

// Refs
const forms = ref([]);
const form = ref({
	name: '',
});

// Initialization
const init = () => {
	forms.value = duplicateVar(props?.fields);
	forms.value = forms?.value?.map((form) => {
		return {
			...form,
			error: '',
		};
	});
};

/**
 * Set error messages for a field, including password and email validation.
 * @param {Object} field - The field object to check.
 * @returns {void}
 * @typedef {Object} Field
 * @property {string} key - The key identifier of the field.
 * @property {string} value - The value of the field.
 * @property {string} error - The error message associated with the field.
 * @description This function sets an error message for the specified field if its value is empty.
 * Additionally, it calls helper functions to perform specific validations for password and email fields.
 * @example const field = { key: 'username', value: 'JohnDoe', error: '' };
 * setError(field);
 */
const setError = (field) => {
	const index = forms?.value?.findIndex((form) => form?.key === field?.key);
	forms.value[index].error =
		field?.value === '' ? 'Inputan Tidak boleh Kosong' : undefined;
	setErrorPassword(field, index);
	setErrorEmail(field, index);
};

/**
 * Set error messages for password and confirm password fields.
 * @param {Object} field - The field object to check.
 * @param {number} index - The index of the field in the forms array.
 * @returns {void}
 * @typedef {Object} Field
 * @property {string} type - The type of the field.
 * @property {string} key - The key identifier of the field.
 * @typedef {Array<Object>} FormsArray
 * @description This function checks if the specified field is either 'password' or 'confirm_password'
 * and sets error messages if the passwords do not match.
 * @example const field = { type: 'password', key: 'password' };
 * const index = 0;
 * setErrorPassword(field, index);
 */
const setErrorPassword = (field, index) => {
	const isPasswordField =
		field?.type === 'password' || field?.type === 'confirm_password';
	const passwordIndex = forms?.value?.findIndex(
		(form) => form?.key === 'password'
	);
	const confirmPasswordIndex = forms?.value?.findIndex(
		(form) => form?.key === 'confirm_password'
	);

	const isValidPasswordIndexes =
		passwordIndex > -1 && confirmPasswordIndex > -1;

	if (isPasswordField && isValidPasswordIndexes) {
		const password = forms?.value?.[passwordIndex]?.value;
		const confirmPassword = forms?.value?.[confirmPasswordIndex]?.value;

		const passwordsNotMatch = password !== confirmPassword;

		forms.value[passwordIndex].error = passwordsNotMatch
			? 'Password Tidak Sama'
			: '';
		forms.value[confirmPasswordIndex].error = passwordsNotMatch
			? 'Password Tidak Sama'
			: '';
	}
};

/**
 * Set error message for the email field.
 * @param {Object} field - The field object to check.
 * @param {number} index - The index of the field in the forms array.
 * @returns {void}
 * @typedef {Object} Field
 * @property {string} key - The key identifier of the field.
 * @property {string} value - The value of the field.
 * @property {string} error - The error message associated with the field.
 * @description This function checks if the specified field has the key 'email' and sets an error message
 * if the email value does not match the specified regular expression for a valid email format.
 * @example const field = { key: 'email', value: 'example@example.com', error: '' };
 * const index = 0;
 * setErrorEmail(field, index);
 */
const setErrorEmail = (field, index) => {
	if (field.key === 'email') {
		const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
		field.error = !emailRegex?.test(field?.value)
			? 'Email tidak valid'
			: field.error;
	}
};

// Event handlers
const inputTimer = ref(null);
const isLoading = ref(false);
const onInput = (event, field) => {
	setError(field);
};

const onInputSelect= (event, field) => {
    isLoading.value = true;
    const isAddressField = field && field?.key;
    if (isAddressField) {
        if (inputTimer.value) {
            // delete timer if exist
            clearTimeout(inputTimer.value);
            inputTimer.value = null;
            isLoading.value = true;
        }

        isLoading.value = false;
        inputTimer.value = setTimeout(() => {
            generateLatAndLong(event.target.value)
        }, 700);
    }
};

const generateLatAndLong = (inputAddress) => {
    const address = inputAddress;

	// Construct the API request URL
	const apiUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(
		address
	)}`;

	// Make a GET request to the Nominatim API
	fetch(apiUrl)
		.then((response) => response.json())
		.then((data) => {
			// Check if the response has results
			if (data.length > 0) {
                forms.value[7].options = data.map(curr => ({
                    label: curr?.display_name,
                    latitude: curr?.lat,
                    longitude: curr?.lon,
                }));
			} else {
                toast?.error('Alamat Tidak ditemukan');
                forms.value[8].error = 'Alamat Tidak ditemukan';
			}
		})
		.catch((error) => {
			console.error('Error fetching geocoding data:', error);
		});
};

// Computed property
const noErrors = computed(
	() =>
		forms?.value?.every(
			(curr) => curr?.error === '' || curr?.error === undefined
		) ?? false
);

// Submit function
const submit = () => {
	forms?.value?.forEach((curr) => setError(curr));
	const noErrors = forms?.value?.every(
		(curr) => curr?.error === '' || curr?.error === undefined
	);
	if (noErrors) {
		emit('submit', forms?.value, props?.type);
	}
};

// Watch effect
watchEffect(() => {
	init();
});
</script>

<style lang="scss" scoped>
.fromGroup {
	ul {
		height: 200px !important;
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
