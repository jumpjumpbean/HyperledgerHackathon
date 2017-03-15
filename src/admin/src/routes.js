import Vue from 'vue';
import Abstract from './pages/common/abstract';
import NotFound from './pages/common/404';

// list with filters page
import ListWithFilters from './pages/list/with-filters';
import BigForm from './pages/form/big-form';
import Login from './pages/login/login';

// Edwin added
// 绝大多数Element-ui的组件

// 基本控件
import Icon from './pages/components/basic/icon';
import Buttons from './pages/components/basic/buttons';
// Form相关控件
import Radio from './pages/components/form/radio';
import Checkbox from './pages/components/form/checkbox';
import Input from './pages/components/form/input';
import InputNumber from './pages/components/form/input-number';
import Select from './pages/components/form/select';
import Switch from './pages/components/form/switch';
import Slider from './pages/components/form/slider';
import TimePicker from './pages/components/form/time-picker';
import DatePicker from './pages/components/form/date-picker';
import DateTimePicker from './pages/components/form/date-time-picker';
import Upload from './pages/components/form/upload';
import Rate from './pages/components/form/rate';
import Form from './pages/components/form/form';
// 数据相关控件
import Table from './pages/components/data/table';
import Tag from './pages/components/data/tag';
import Process from './pages/components/data/process';
import Tree from './pages/components/data/tree';

const root = Vue.component('root', {
  template: '<router-view></router-view>'
});

let routes = [
  {
    path: '/login',
    component: Login,
    name: 'login',
    meta: {
      hidden: true
    }
  },
  {
    path: '/404',
    component: NotFound,
    name: '404',
    meta: {
      requiresAuth: true
    }
  },
  {
    path: '/',
    component: root,
    meta: {
      requiresAuth: true
    },
    children: [
      {
        path: 'list',
        component: Abstract,
        name: '积分管理',
        iconClass: 'el-icon-message',
        children: [
          {
            path: 'filters',
            name: '交易查询',
            component: ListWithFilters,
            imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
          },
          {
            path: 'filters',
            name: '结算',
            component: ListWithFilters,
            imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
          },
          {
            path: 'filters',
            name: '统计',
            component: ListWithFilters,
            imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
          },
          {
            path: 'filters',
            name: '设置',
            component: ListWithFilters,
            imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
          }
        ]
      },
     /* 
      {
        path: 'form',
        component: Abstract,
        name: '表单',
        iconClass: 'el-icon-document',
        children: [
          {
            path: 'big-form',
            name: '简历管理',
            component: BigForm,
            imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
          }
        ]
      },
      {
        path: 'all-components',
        component: Abstract,
        name: '所有控件',
        iconClass: 'el-icon-menu',
        children: [
          {
            path: 'basic',
            component: Abstract,
            name: '基础组件',
            // iconClass: 'el-icon-menu',
            //message用于展示只读label,要占用一层children
            message: 'Basic',
            children: [
              {
                path: 'icon',
                name: 'Icon 图标',
                component: Icon,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'buttons',
                name: 'Button 按钮',
                component: Buttons,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              }
            ]
          },
          {
            path: 'form',
            component: Abstract,
            name: '表单组件',
            // iconClass: 'el-icon-menu',
            message: 'Form',
            children: [
              {
                path: 'radio',
                name: 'Radio 单选框',
                component: Radio,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'checkbox',
                name: 'Checkbox 多选框',
                component: Checkbox,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'input',
                name: 'Input 输入框',
                component: Input,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'input-number',
                name: 'InputNumber 计数器',
                component: InputNumber,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'select',
                name: 'Select 选择器',
                component: Select,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'switch',
                name: 'Switch 开关',
                component: Switch,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'slider',
                name: 'Slider 滑块',
                component: Slider,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'time-picker',
                name: 'TimePicker 时间选择器',
                component: TimePicker,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'date-picker',
                name: 'DatePicker 日期选择器',
                component: DatePicker,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'date-time-picker',
                name: 'DateTimePicker 日期时间选择器',
                component: DateTimePicker,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'upload',
                name: 'Upload 上传',
                component: Upload,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'rate',
                name: 'Rate 评分',
                component: Rate,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'form',
                name: 'Form 表单',
                component: Form,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              }
            ]
          },
          {
            path: 'data',
            component: Abstract,
            name: '数据展示组件',
            // iconClass: 'el-icon-menu',
            //message用于展示只读label,要占用一层children
            message: 'Data',
            children: [
              {
                path: 'table',
                name: 'Table 表格',
                component: Table,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'tag',
                name: 'Tag 标签',
                component: Tag,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'process',
                name: 'Progress 进度条',
                component: Process,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'tree',
                name: 'Tree 树形控件',
                component: Tree,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'tag',
                name: 'Tag 标签',
                component: Tag,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'tag',
                name: 'Tag 标签',
                component: Tag,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              },
              {
                path: 'tag',
                name: 'Tag 标签',
                component: Tag,
                imgUrl: 'https://o0p2g4ul8.qnssl.com/vsite%2Fbackground.jpg'
              }
            ]
          },
        ]
      }
      */
    ]
  },
  {
    path: '*',
    redirect: {path: '/404'}
  }
];
let menuCount = routes.length;
routes[menuCount - 2].children.forEach(route => {
  if (route.children) {
    if (!route.meta) route.meta = {};
    route.meta.children = route.children;
  }
});

export default routes;
