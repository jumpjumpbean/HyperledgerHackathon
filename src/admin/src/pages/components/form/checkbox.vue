<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
        <el-checkbox v-model="checked" checked>备选项</el-checkbox>

        <br><br>

        <el-checkbox v-model="checked1" disabled>备选项1</el-checkbox>
        <el-checkbox v-model="checked2" disabled>备选项</el-checkbox>

        <br><br>
        
        <el-checkbox-group v-model="checkList">
          <el-checkbox label="复选框 A"></el-checkbox>
          <el-checkbox label="复选框 B"></el-checkbox>
          <el-checkbox label="复选框 C"></el-checkbox>
          <el-checkbox label="禁用" disabled></el-checkbox>
          <el-checkbox label="选中且禁用" disabled></el-checkbox>
        </el-checkbox-group>
    </div>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        checked: false,
        checked1: false,
        checked2: true,
        checkList: ['选中且禁用','复选框 A']
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