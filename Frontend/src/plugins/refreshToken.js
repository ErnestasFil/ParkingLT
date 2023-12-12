import axios from 'axios';
import store from './store';
import { useToast } from 'vue-toastification';
import VueJwtDecode from 'vue-jwt-decode';

const authService = {
  async refreshToken(router) {
    const toast = useToast();

    try {
      const response = await axios.post(
        `${process.env.APP_URL}/refresh`,
        {},
        {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );

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
    } catch (error) {
      toast.error('Prašome prisijungti iš naujo!', {
        timeout: 10000,
      });
      store.commit('removeUserData');
      router.push({ name: 'Home' });
    }
  },
};

export default authService;
