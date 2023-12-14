import axios from 'axios';
import store from './store';
import { useToast } from 'vue-toastification';
import VueJwtDecode from 'vue-jwt-decode';

const authService = {
  async refreshToken(router) {
    const toast = useToast();
    await axios
      .post(
        `${process.env.APP_URL}/refresh`,
        {},
        {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      )
      .then((response) => {
        if (response.status === 200) {
          const info = response.data;
          const decoded = VueJwtDecode.decode(info.token);
          const token = {
            token: info.token,
            exp: decoded.exp,
            sub: decoded.sub,
            email: decoded.email,
            role: decoded.role,
          };
          store.commit('jwt', token);
        }
      })
      .catch((error) => {
        toast.error('Prašome prisijungti iš naujo!', {
          timeout: 10000,
        });
        store.commit('removeUserData');
        router.push({ name: 'Home' });
      });
  },
  async error403(error, router) {
    const toast = useToast();
    if (error.response && error.response.status === 403) {
      toast.error('Prieiga negalima!', {
        timeout: 10000,
      });
      router.push({ name: 'Home' });
    }
  },
  async error404(error, router) {
    const toast = useToast();
    if (error.response && error.response.status === 404) {
      toast.error(error.response.data.message, {
        timeout: 10000,
      });
      router.push({ name: 'Home' });
    }
  },
  async errorOther(error, router) {
    const toast = useToast();
    console.log(error);
    if (error.response && error.response.status === 500) {
      toast.error('Serverio klaida', {
        timeout: 10000,
      });
    } else if (error.response && error.response.status !== 404 && error.response.status !== 403) {
      toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
        timeout: 10000,
      });
      router.push({ name: 'Home' });
    } else {
      toast.error('Serverio klaida', {
        timeout: 10000,
      });
    }
  },
};

export default authService;
