<template>
	<div class="space-y-5">
        <VueAllert type="success" dismissible icon="akar-icons:double-check">
            Selamat datang, {{form?.name}}! Akun Anda telah berhasil terdaftar di sistem kami. Untuk dapat mengakses sistem dengan lebih baik, mohon segera lengkapi profil Anda. Terima kasih atas kerjasama Anda.
        </VueAllert>
		<Card title="Profile">
			<div class="">
				<InputField
					v-model="form.name"
					label="Nama"
					placeholder="Masukan Nama"
				/>
				<InputField
					v-model="form.email"
					label="Email"
					placeholder="Masukan Email"
				/>
				<InputField
					v-model="form.phoneNumber"
					label="Nomor Telepon"
					placeholder="Masukan Nomor Telepon"
				/>
				<InputTextArea 
					v-model="form.address" 
					label="Alamat" 
					placeholder="Masukan Alamat" 
				/>
				<label>Upload Foto Profile</label>
				<DropZoneVue @upload="upload" />
			</div>
			<div class="flex justify-end mt-3">
				<vue-button text="Update" btn-class="btn-sm btn btn-dark" :is-loading="isLoading" @click="submit" />
			</div>
		</Card>
	</div>
</template>
<script>
import Card from '@/components/Card';
import HorizentalWizard from '@/components/FormWizard/Horizontal.vue';
import VueAllert from '@/components/Alert';
import InputField from '@/components/Textinput';
import { useUserStore } from "@/store/user";
import { useToast } from 'vue-toastification';
import { useRoute, useRouter } from 'vue-router';
import { onMounted, watch, ref } from 'vue';
import { duplicateVar } from '@/constant/helpers';
import InputTextArea from '@/components/Textarea';
import VueButton from '@/components/Button';
import VueSelect from '@/components/Select/VueSelect.vue';
import DropZoneVue from '@/components/Fileinput/DropZone.vue';
import { uploadMedia } from '@/helpers/media';
import userApi from '@/helpers/user';

export default {
	components: {
		HorizentalWizard,
        Card,
        VueAllert,
		InputField,
		InputTextArea,
		VueButton,
		VueSelect,
		DropZoneVue,
	},

	data() {
		return {
			genderOptions: ['Laki-Laki', 'Perempuan'],
		}
	},
	
	setup() {
		const toast = useToast();
		const router = useRouter();
		const route = useRoute();
		const store = useUserStore();
		const user =  store?.user || JSON.parse(localStorage.getItem('users'));
		const dataUser = user;
		const form = ref();
		const alertText = ref('');
		const isLoading = ref(false);
		form.value = {
			name: user?.name ?? '',
			email: user?.email ?? '',
			phoneNumber: user?.profile?.phoneNumber ?? '',
			address: user?.profile?.address ?? '',
		};
		const checkCapabilities = () => {
			if (user?.status !== 'active') {
				toast?.error('Lengkapi data anda !!');
				router?.push('/on-boarding');
			}
		};

		const getMe = () => {
			const params = {
				entities: 'profile.medias',
			};
			const callback = (res) => {
				if (res?.data?.meta?.status) {
					const data = res?.data?.data;
					store.setUser(data);
					localStorage.setItem('users', JSON.stringify(data));
				}
			};
			const err = (e) => {};
			userApi.getMe(params, callback, err);
		}

		const mediaId = ref();
		const isFetching = ref(false);
		const upload = (media) => {
			isFetching.value = true;
			const formData = new FormData();
			formData.append('media', media[0]);
			formData.append('type', 'profile');
			
			const callback = (res) => {
				isFetching.value = false;
				mediaId.value = res.data.data.id;
			};
			const err = (e) => {
				isFetching.value = true;
				console.log('e => ', e);
			};

			uploadMedia(formData, callback, err);
		};
		
		watch(() => route?.path, () => {
			checkCapabilities();
			getMe();
		})

		onMounted(() => {
			checkCapabilities();
			getMe();
		});

		const password = ref();
		const confirmPassword = ref();

		const submit = () => {
			const params = {
				...form.value,
				mediaId: mediaId?.value,
				password: password?.value ?? null,
			}
			isLoading.value = true;
			const callback = (res) => {
				if (res?.data?.meta?.status) {
					toast?.success('Update Profile Berhasil');
					isLoading.value = false;
					router?.push('/');
					store.setUser(res?.data?.data);
				}
			};
			const err = (e) => {};
			userApi.updateProfile(params, callback, err);
		};

		const onInputGender = (e) => {
			// console.log(e?.target?.value);
		}

		return {
			dataUser,
			form,
			upload,
			submit,
			isFetching,
			alertText,
			onInputGender,
			password,
			confirmPassword,
			isLoading,
		}
	},
};
</script>
<style lang=""></style>
