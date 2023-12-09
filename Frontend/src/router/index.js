// Composables
import { createRouter, createWebHistory } from 'vue-router';
import Default from '../layouts/default/Default.vue';
import Home from '../views/Home';
import Login from '../views/Login';
import Register from '../views/Register';
import ParkingZone from '../views/ParkingZone';
import EditParkingZone from '../views/EditParkingZone';
import ParkingZoneAdd from '../views/ParkingZoneAdd';
import ParkingSpace from '../views/ParkingSpace';
const routes = [
  {
    path: '/',
    component: Default,
    children: [
      {
        path: '',
        name: 'Home',
        component: Home,
      },
    ],
  },
  {
    path: '/login',
    component: Default,
    children: [
      {
        path: '',
        name: 'Login',
        component: Login,
      },
    ],
  },
  {
    path: '/register',
    component: Default,
    children: [
      {
        path: '',
        name: 'Register',
        component: Register,
      },
    ],
  },
  {
    path: '/parking_zone',
    component: Default,
    children: [
      {
        path: '',
        name: 'ParkingZone',
        component: ParkingZone,
      },
    ],
  },
  {
    path: '/parking_zone/:id/parking_space',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'ParkingSpace',
        component: ParkingSpace,
      },
    ],
  },
  {
    path: '/parking_zone/:id',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'EditParkingZone',
        component: EditParkingZone,
      },
    ],
  },
  {
    path: '/parking_zone_add',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'ParkingZoneAdd',
        component: ParkingZoneAdd,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
