<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
      <el-switch
        v-model="value1"
        on-text=""
        off-text="">
      </el-switch>
      <el-switch
        v-model="value2"
        on-color="#13ce66"
        off-color="#ff4949">
      </el-switch>
    </div>

  </div>
</template>

<script>
export default {
    data() {
      return {
        value1: true,
        value2: true
      }
    }
  };
</script>

<style lang="scss" scoped>
</style>