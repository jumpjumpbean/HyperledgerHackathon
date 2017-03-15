<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
      <el-input-number v-model="num1" @change="handleChange" :min="1" :max="10"></el-input-number>

      <br><br>

      <el-input-number v-model="num2" :disabled="true"></el-input-number>

      <br><br>

      <el-input-number v-model="num3" :step="5"></el-input-number>

      <br><br>
    </div>

  </div>
</template>

<script>
export default {
    data() {
      return {
        num1: 1,
        num2: 2,
        num3: 3
      };
    },
    methods: {
      handleChange(value) {
        console.log(value);
      }
    }
  };
</script>

<style lang="scss" scoped>
</style>