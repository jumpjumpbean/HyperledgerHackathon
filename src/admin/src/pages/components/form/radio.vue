<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">

        <el-radio class="radio" v-model="radio1" label="1">备选项</el-radio>
        <el-radio class="radio" v-model="radio1" label="2">备选项</el-radio>

        <br><br>

        <el-radio disabled v-model="radio2" label="禁用">备选项</el-radio>
        <el-radio disabled v-model="radio2" label="选中且禁用">备选项</el-radio>

        <br><br>

        <el-radio-group v-model="radio3" @change="handleRadio3Change" >
          <el-radio :label="3">备选项</el-radio>
          <el-radio :label="6">备选项</el-radio>
          <el-radio :label="9">备选项</el-radio>
        </el-radio-group>

        <br><br>

        <el-radio-group v-model="radio4">
          <el-radio-button label="上海"></el-radio-button>
          <el-radio-button label="北京"></el-radio-button>
          <el-radio-button label="广州" :disabled="true"></el-radio-button>
          <el-radio-button label="深圳"></el-radio-button>
        </el-radio-group>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        radio1: 1,
        radio2: '选中且禁用',
        radio3: 3,
        radio4: '',
      }
    },
    methods: {
      handleRadio3Change(label) {
        console.log()
      }
    }
  }
</script>

<style lang="scss" scoped>
</style>