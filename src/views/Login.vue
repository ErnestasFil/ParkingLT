<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-card max-width="600" class="mx-auto text-center" title="Prisijungimas">
        <v-form @submit.prevent="login">
          <v-text-field v-model="data.email" label="El. paštas" :error-messages="errors.email"></v-text-field>
          <v-text-field v-model="data.password" type="password" label="Slaptažodis" :error-messages="errors.password"></v-text-field>
          <v-btn :loading="data.loading" type="submit" block class="mt-2" text="Prisijungti"></v-btn>
        </v-form>
      </v-card>
    </v-responsive>
  </v-container>
</template>

<script>
import { reactive } from 'vue';
import axios from 'axios';
import store from '../plugins/store';
import VueJwtDecode from 'vue-jwt-decode';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
export default {
  setup() {
    const toast = useToast();
    const data = reactive({ loading: false, email: '', password: '' });
    const errors = {};
    const router = useRouter();
    const login = async () => {
      Object.keys(errors).forEach((key) => delete errors[key]);
      data.loading = true;
      await axios
        .post(`${process.env.APP_URL}/login`, data, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        })
        .then((data) => {
          if (data.status === 200) {
            const info = data.data;
            const decoded = VueJwtDecode.decode(info.access_token);
            const token = {
              token: info.access_token,
              exp: decoded.exp,
              sub: decoded.sub,
              email: decoded.email,
              role: decoded.role,
            };
            store.commit('jwt', token);
            toast.success(info.message, {
              timeout: 10000,
            });
            router.push({ name: 'Home' });
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 422) {
            const info = error.response.data;
            Object.assign(errors, info);
          } else if (error.response && error.response.status === 401) {
            const info = error.response.data;
            toast.error(info.message, {
              timeout: 10000,
            });
            data.password = '';
          } else {
            refresh.error403(error, router);
            refresh.error404(error, router);
            refresh.errorOther(error, router);
          }
        });
      data.loading = false;
    };

    return {
      data,
      errors,
      login,
    };
  },
};
</script>
