<template>
  <div id="big-form">
    <!-- breadcrumb start  -->
    <db-breadcrumb></db-breadcrumb>
    <!-- breadcrumb end  -->

    <div class="db-content-inner">
      <el-time-select
        v-model="value1"
        :picker-options="{
          start: '08:30',
          step: '00:15',
          end: '18:30'
        }"
        placeholder="选择时间">
      </el-time-select>

      <br><br>

      <el-time-picker
        v-model="value2"
        :picker-options="{
          selectableRange: '18:30:00 - 20:30:00'
        }"
        placeholder="任意时间点">
      </el-time-picker>

      <br><br>

      <el-time-select
        placeholder="起始时间"
        v-model="startTime"
        :picker-options="{
          start: '08:30',
          step: '00:15',
          end: '18:30'
        }">
      </el-time-select>
      <el-time-select
        placeholder="结束时间"
        v-model="endTime"
        :picker-options="{
          start: '08:30',
          step: '00:15',
          end: '18:30',
          minTime: startTime
        }">
      </el-time-select>

      <br><br>

      <el-time-picker
        is-range
        v-model="value3"
        placeholder="选择时间范围">
      </el-time-picker>

    </div>

  </div>
</template>

<script>
export default {
    data() {
      return {
        value1: '',
        value2: new Date(2016, 9, 10, 18, 40),
        startTime: '',
        endTime: '',
        value3: [new Date(2016, 9, 10, 8, 40), new Date(2016, 9, 10, 9, 40)]
      };
    }
  }
</script>

<style lang="scss" scoped>
</style>