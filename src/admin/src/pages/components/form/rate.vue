<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
      <div class="block">
        <span class="demonstration">默认不区分颜色</span>
        <el-rate v-model="value1"></el-rate>
      </div>
      <div class="block">
        <span class="demonstration">区分颜色</span>
        <el-rate
          v-model="value2"
          :colors="['#99A9BF', '#F7BA2A', '#FF9900']">
        </el-rate>
      </div>

      <br><br>

      <el-rate
        v-model="value3"
        show-text>
      </el-rate>

      <br><br>

      <el-rate
        v-model="value5"
        disabled
        show-text
        text-color="#ff9900"
        text-template="{value}">
      </el-rate>

    </div>

  </div>
</template>

<script>
export default {
    data() {
      return {
        value1: null,
        value2: null,
        value3: null,
        value5: 3.7
      }
    }
  }
</script>

<style lang="scss" scoped>
</style>