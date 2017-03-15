<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
      <div class="block">
        <span class="demonstration">默认</span>
        <el-slider v-model="value1"></el-slider>
      </div>
      <div class="block">
        <span class="demonstration">自定义初始值</span>
        <el-slider v-model="value2"></el-slider>
      </div>
      <div class="block">
        <span class="demonstration">禁用</span>
        <el-slider v-model="value3" disabled></el-slider>
      </div>

      <br><br>

      <div class="block">
        <span class="demonstration">不显示间断点</span>
        <el-slider
          v-model="value4"
          :step="10">
        </el-slider>
      </div>
      <div class="block">
        <span class="demonstration">显示间断点</span>
        <el-slider
          v-model="value5"
          :step="10"
          show-stops>
        </el-slider>
      </div>

      <br><br>

      <div class="block">
        <el-slider
          v-model="value6"
          show-input>
        </el-slider>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    data() {
      return {
        value1: 0,
        value2: 50,
        value3: 42,
        value4: 0,
        value5: 0,
        value6: 0
      }
    }
  }
</script>

<style lang="scss" scoped>
.el-slider {
  width : 300px
}
</style>