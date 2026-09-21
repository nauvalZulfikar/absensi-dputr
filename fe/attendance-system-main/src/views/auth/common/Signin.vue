<template>
	<form @submit.prevent="onSubmit" class="space-y-4">
		<Textinput label="Email" type="email" placeholder="Type your email" name="emil" v-model="email" :error="emailError"
			classInput="h-[48px]" />
		<Textinput label="Password" type="password" placeholder="8+ characters, 1 capitat letter " name="password"
			v-model="password" hasicon classInput="h-[48px]" />

		<vue-button text="SIgn In" btn-class="btn btn-dark block w-full text-center" :is-loading="isLoading"
			:is-Disabled="isLoading" @click.prevent="onSubmit" />
	</form>
</template>
<script>
// import reCaptcha from '@/components/reCaptcha.vue';
import Textinput from '@/components/Textinput';
import VueButton from '@/components/Button/index.vue';
import { useField, useForm } from 'vee-validate';
import * as yup from 'yup';

import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import authApi from '@/helpers/auth.js';
import client from '@/helpers/http-client';
import { ref, computed } from 'vue';
import { VueReCaptcha, useReCaptcha } from 'vue-recaptcha-v3';
import axios from 'axios';

export default {
	components: {
		VueButton,
		Textinput,
		VueReCaptcha,
		// reCaptcha
	},
	data() {
		return {
			checkbox: false,
		};
	},
	setup() {
		const { executeRecaptcha, recaptchaLoaded } = useReCaptcha();
		const token = ref(null);

		// Membuat computed property untuk fungsi recaptcha
		const recaptcha = computed(async () => {
			// (optional) Tunggu hingga recaptcha telah dimuat.
			await recaptchaLoaded();

			// Eksekusi reCAPTCHA dengan aksi "login".
			const generatedToken = await executeRecaptcha('LOGIN');

			// Lakukan sesuatu dengan token yang diterima.
			// Di sini, token dapat diakses melalui recaptcha.value.
			token.value = generatedToken;

			return generatedToken;
		});
		const isLoading = ref(false);
		// Define a validation schema
		const schema = yup.object({
			email: yup.string().required('Email is required').email(),
			password: yup.string().required('Password is required'),
		});

		const toast = useToast();
		const router = useRouter();

		const formValues = {
			email: '',
			password: '',
		};

		const { handleSubmit } = useForm({
			validationSchema: schema,
			initialValues: formValues,
		});
		// No need to define rules for fields

		const { value: email, errorMessage: emailError } = useField('email');
		const { value: password, errorMessage: passwordError } =
			useField('password');


		const onSubmit = handleSubmit(async (values) => {

			await recaptchaLoaded();

			// Eksekusi reCAPTCHA dengan aksi "login".
			const generatedToken = await executeRecaptcha('LOGIN');
			isLoading.value = true;
			const email = values.email;
			const password = values.password;
			const resonsesrecaptcha = generatedToken;

			const params = {
				email,
				password,
				'g-recaptcha-response': resonsesrecaptcha,
			};

			const url = import.meta.env.VITE_AUTH_API_URL;
			axios.post(`${url}/auth/login`, params).then((res) => {
				isLoading.value = false;

				const data = res.data;
				if (data.meta.status) {
					const responseUser = data?.data?.access_token?.user;
					localStorage.setItem('users', JSON.stringify(responseUser));
					checkCapabilities(responseUser);
					toast.success(' Login  successfully', {
						timeout: 2000,
					});

					const token = data.data.access_token?.token;
					localStorage.setItem('token', token);
					client.defaults.headers.Authorization = `Bearer ${token}`;
					const isAdminMode = responseUser?.roles?.some(curr => curr?.roleId === "1" || curr?.roleId === "3");
					if (isAdminMode) {
						router?.push('/admin/division');
					} else {
						router?.push('/');
					}
				} else {
					isLoading.value = false;
					toast.error(data.meta.message, {
						timeout: 2000,
					});
				}
			}).catch(e => {
				isLoading.value = false;
				console.log(e);
			});
		});

		const checkCapabilities = (user) => {
			const isAdminMode = ['superadmin', 'admin'];

			const checkCapabilities = user?.roles?.some((role) =>
				isAdminMode.includes(role?.role?.name)
			);
			checkUserIsNewAccount(user);

			if (checkCapabilities) {
				router.replace('/admin/division');
			} else {
				router.replace('/');
			}
		};

		const checkUserIsNewAccount = (user) => {
			if (!user.profile) {
				router.push('/on-boarding');
			}
		};

		return {
			email,

			emailError,
			password,
			passwordError,
			onSubmit,
			isLoading,
			recaptcha,
		};
	},
};
</script>
<style lang="scss">
</style>
