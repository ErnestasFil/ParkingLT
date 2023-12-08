// Composables
import { createRouter, createWebHistory } from 'vue-router';
import Default from '../layouts/default/Default.vue';
import Home from '../views/Home';
import Login from '../views/Login';
import Register from '../views/Register';
import Logout from '../components/Logout';
import ParkingZone from '../views/ParkingZone';
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
    path: '/',
    component: Default,
    children: [
      {
        path: '',
        name: 'Logout',
        component: Logout,
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
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
