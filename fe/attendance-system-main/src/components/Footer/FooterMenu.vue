<template lang="">
  <div
    class="bg-white bg-no-repeat custom-dropshadow footer-bg dark:bg-slate-700 flex justify-around items-center backdrop-filter backdrop-blur-[40px] fixed left-0 w-full z-[9999] bottom-0 py-[12px] px-4"
  >
    <router-link to="/divisions" v-slot="{ isActive }">
      <span
        class="relative cursor-pointer rounded-full text-[20px] flex flex-col items-center justify-center text-slate-900"
        ><Icon icon="solar:home-bold" />
      </span>
      <span
        class="block text-[11px] text-slate-900"
        >Home</span
      >
    </router-link>
    <router-link
      v-slot="{ isActive }"
      to="/"
      class="relative bg-white bg-no-repeat backdrop-filter backdrop-blur-[40px] rounded-full footer-bg dark:bg-slate-700 h-[65px] w-[65px] z-[-1] -mt-[40px] flex justify-center items-center"
    >
      <div
        class="h-[50px] w-[50px] rounded-full relative left-[0px] top-[0px] custom-dropshadow"
      >
        <img
          :src="profileImage"
          alt=""
          class="w-full h-full rounded-full"
        />
      </div>
    </router-link>
    <router-link to="/logout" v-slot="{ isActive }">
      <span
        class="relative cursor-pointer rounded-full text-[20px] flex flex-col items-center justify-center text-slate-900">
        <Icon icon="heroicons-outline:login" />
      </span>
      <span
        class="block text-[11px] text-slate-900"
        >Logout</span
      >
    </router-link>
  </div>
</template>
<script>
import Icon from "@/components/Icon";
import { computed } from "vue";
import { useUserStore } from '@/store/user';
import { userDummyImage } from "@/constant/static";
export default {
  components: {
    Icon,
  },
  setup() {
    const store = useUserStore();
    const user = computed(() => store?.user);
    const profileImage = user?.profile?.medias?.url ?? userDummyImage;

    return {
      profileImage,
    }
  },
};
</script>
<style lang="scss"></style>
