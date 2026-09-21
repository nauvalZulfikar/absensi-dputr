import { defineStore } from "pinia";
import authApi from '@/helpers/auth';
import { useToast } from "vue-toastification";
import { useRouter } from "vue-router";

const toast = useToast();
const router = useRouter();

const _wrapper = {
    state: () => ({}),
    actions: {
        logout(){
            const callback = (res) => {
                if (res?.data?.meta?.status) {
                    this.$router.push('/login');
                    this.$toast.success('Berhasil Logout');
                }
            };
            const err = (e) => {
                console.error("🚀 ~ file: Logout.vue:10 ~ err ~ e:", e);
            };
        
            authApi.logout({}, callback, err);
        },
    },
}

export const useGlobalStore = defineStore('globalStore', _wrapper);
