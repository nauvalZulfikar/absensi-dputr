<template>
  <div :class="customClass" v-if="!isLoading">
    <img src="@/assets/images/svg/loader.svg" alt="" />
  </div>
</template>
<script>
export default {
  name: "LoaderElement",
  props: {
    customClass: {
      type: String,
      default: 'h-20 w-20 mx-auto',
    }
  },
  data() {
    return {
      isLoading: false,
    };
  },
  mounted() {
    // onreadystatechange
    document.onreadystatechange = () => {
      if (document.readyState === "complete") {
        this.isLoading = true;
      }
    };
  },
};
</script>
<style lang=""></style>
