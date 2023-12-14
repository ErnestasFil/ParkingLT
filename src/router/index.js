import { createRouter, createWebHistory } from 'vue-router';
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
import AllReservations from '../views/Reservations';
import Users from '../views/Users';
import Profile from '../views/Profile';
import NotFound from '../views/NotFound';
import VueJwtDecode from 'vue-jwt-decode';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { noAuth: true },
  },
  {
    path: '/register',

    name: 'Register',
    component: Register,
    meta: { noAuth: true },
  },
  {
    path: '/parking_zone',
    name: 'ParkingZone',
    component: ParkingZone,
  },
  {
    path: '/parking_zone/:id/parking_space',
    props: true,
    name: 'ParkingSpace',
    component: ParkingSpace,
  },
  {
    path: '/parking_zone/:id',
    props: true,
    name: 'EditParkingZone',
    component: EditParkingZone,
    meta: { auth: true, admin: true },
  },
  {
    path: '/parking_zone_add',
    name: 'ParkingZoneAdd',
    component: ParkingZoneAdd,
    meta: { auth: true, admin: true },
  },
  {
    path: '/parking_zone/:id/parking_space_add',
    props: true,
    name: 'ParkingSpaceAdd',
    component: ParkingSpaceAdd,
    meta: { auth: true, admin: true },
  },
  {
    path: '/parking_zone/:parking_zone/parking_space/:parking_space',
    props: true,
    name: 'EditParkingSpace',
    component: EditParkingSpace,
    meta: { auth: true, admin: true },
  },
  {
    path: '/parking_zone/:parking_zone/parking_space/:parking_space/reservation',
    props: true,
    name: 'Reservation',
    component: Reservation,
    meta: { auth: true },
  },
  {
    path: '/user/:id/reservation',
    props: true,
    name: 'UserReservations',
    component: UserReservations,
    meta: { auth: true },
  },
  {
    path: '/user/:id/reservations',
    name: 'AllReservations',
    component: AllReservations,
    meta: { auth: true, admin: true },
  },
  {
    path: '/users',
    name: 'Users',
    component: Users,
    meta: { auth: true, admin: true },
  },
  {
    path: '/user/:id',
    props: true,
    name: 'Profile',
    component: Profile,
    meta: { auth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    props: true,
    name: 'NotFound',
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes: routes,
});

router.beforeEach((to, from) => {
  const token = localStorage.getItem('token');
  const toast = useToast();
  if (to.meta.auth && token) {
    const decodedToken = VueJwtDecode.decode(token);
    if (decodedToken.exp < Math.floor(Date.now() / 1000)) {
      refresh.refreshToken(router);
    } else if (to.meta.admin && decodedToken.role !== 'Administrator') {
      toast.error('Prieiga negalima!', {
        timeout: 10000,
      });
      return { name: 'Home' };
    }
  } else if (to.meta.auth && !token) {
    toast.error('Prašome prisijungti iš naujo!', {
      timeout: 10000,
    });
    localStorage.removeItem('token');
    return { name: 'Login' };
  } else if (to.meta.noAuth && token) {
    toast.error('Prieiga negalima!', {
      timeout: 10000,
    });
    return { name: 'Home' };
  }
});

export default router;
