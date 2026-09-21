<template>
	<div class="space-y-5">
		<Card title="Profile">
			<div class="">
				<InputField
					v-model="password"
					label="Password Lama"
					placeholder="Masukan Password Lama"
					type="password"
					hasicon
				/>
				<InputField
                    class="mt-4"
					v-model="newPassword"
					label="Password Baru"
					placeholder="Masukan Password Baru"
					type="password"
					hasicon
				/>
			</div>
			<div class="flex justify-end mt-3">
				<vue-button 
                    text="Update" 
                    btn-class="btn-sm btn btn-dark" 
                    @click="submit" 
                />
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
	setup() {
		const toast = useToast();
		const router = useRouter();
		const route = useRoute();
		const store = useUserStore();
		const user =  JSON.parse(localStorage.getItem('users')) || store?.user;
		const dataUser = user;
		const form = ref();
		const alertText = ref('');
		form.value = {
			name: user?.name,
			email: user?.email,
			phoneNumber: user?.profile?.phoneNumber ?? '',
			address: user?.profile?.address ?? '',
		};
		const checkCapabilities = () => {
			if (user?.status !== 'active') {
				toast?.error('Lengkapi data anda !!');
				router?.push('/change-password');
			}
		};

		const getMe = () => {
			const params = {
				entities: 'profile.medias',
			};
			const callback = (res) => {
				if (res?.data?.meta?.status) {
					const data = res?.data?.data;
					form?.value 
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
		})

		onMounted(() => {
			checkCapabilities();
			getMe();
		});

		const password = ref();
		const newPassword = ref();

		const submit = () => {
			const params = {
				currentPassword: password?.value,
                newPassword: newPassword?.value
			}
			const callback = (res) => {
				if (res?.data?.meta?.status) {
                    const user = res?.data?.data?.user;
                    if (!user?.profile) {
                        router.push('/on-boarding');
                    }
					toast?.success('Update Profile Berhasil');
                    password.value = '';
                    newPassword.value = '';
				}
			};
			const err = (e) => {};
			userApi.changePassword(params, callback, err);
		};

		const onInputGender = (e) => {
			console.log(e?.target?.value);
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
			newPassword,
		}
	},
};
</script>
<style lang=""></style>
