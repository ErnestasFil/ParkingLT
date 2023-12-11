import { createRouter, createWebHistory } from 'vue-router';
import Default from '../layouts/default/Default.vue';
import Home from '../views/Home';
import Login from '../views/Login';
import Register from '../views/Register';
import ParkingZone from '../views/ParkingZone';
import EditParkingZone from '../views/EditParkingZone';
import ParkingZoneAdd from '../views/ParkingZoneAdd';
import ParkingSpace from '../views/ParkingSpace';
import ParkingSpaceAdd from '../views/ParkingSpaceAdd';
import EditParkingSpace from '../views/EditParkingSpace';
import Reservation from '../views/Reservation';
import UserReservations from '../views/UserReservations';
import AllReservations from '../views/reservations';
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
  {
    path: '/parking_zone/:id/parking_space_add',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'ParkingSpaceAdd',
        component: ParkingSpaceAdd,
      },
    ],
  },
  {
    path: '/parking_zone/:parking_zone/parking_space/:parking_space',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'EditParkingSpace',
        component: EditParkingSpace,
      },
    ],
  },
  {
    path: '/parking_zone/:parking_zone/parking_space/:parking_space/reservation',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'Reservation',
        component: Reservation,
      },
    ],
  },
  {
    path: '/user/:id/reservation',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'UserReservations',
        component: UserReservations,
      },
    ],
  },
  {
    path: '/user/:id/reservations',
    component: Default,
    props: true,
    children: [
      {
        path: '',
        name: 'AllReservations',
        component: AllReservations,
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;
