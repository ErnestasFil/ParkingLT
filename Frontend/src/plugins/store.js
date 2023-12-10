import { createStore } from 'vuex';
import VueJwtDecode from 'vue-jwt-decode';

const store = createStore({
  state: {
    alert: {
      show: false,
      type: '',
      title: '',
      text: '',
      timeout: 0,
    },
    login: {
      token: localStorage.getItem('token') || undefined,
      exp: undefined,
      sub: undefined,
      email: undefined,
      role: undefined,
    },
  },
  mutations: {
    setAlert(state, alert) {
      state.alert = alert;
    },
    resetAlert(state) {
      state.alert = {
        show: false,
        type: '',
        title: '',
        text: '',
        timeout: 0,
      };
    },
    jwt(state, data) {
      state.login = data;
      localStorage.setItem('token', data.token);
    },
    updateUserData(state, token) {
      if (token) {
        const decoded = VueJwtDecode.decode(token);
        state.login = {
          token,
          exp: decoded.exp,
          sub: decoded.sub,
          email: decoded.email,
          role: decoded.role,
        };
      } else {
        localStorage.removeItem('token');
        state.login = {
          token: undefined,
          exp: undefined,
          sub: undefined,
          email: undefined,
          role: undefined,
        };
      }
    },
    
  },
  getters: {
    isAuthenticated: (state) => !!state.login.token,
    isAdmin: (state) => !!state.login.token && state.login.role === 'Administrator',
  },
});

store.commit('updateUserData', localStorage.getItem('token'));

export default store;
