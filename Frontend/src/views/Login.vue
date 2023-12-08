<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
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
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import AlertComponent from '../components/Alert.vue';
import store from '../plugins/store';
import VueJwtDecode from 'vue-jwt-decode';
import { useRouter } from 'vue-router';

export default {
  components: {
    AlertComponent,
  },
  setup() {
    const data = reactive({ loading: false, email: '', password: '' });
    const errors = {};
    const router = useRouter();
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });

    const login = async () => {
      try {
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
              const loginAlert = {
                show: true,
                type: 'success',
                title: info.message,
                text: '',
                timeout: 10000,
              };
              store.commit('setAlert', loginAlert);
              router.push({ name: 'Home' });
            }
          });
      } catch (error) {
        alert.show = false;
        if (error.response && error.response.status === 422) {
          const info = error.response.data;
          Object.assign(errors, info);
        } else {
          alert.type = 'error';
          alert.title = 'Prisijungimo klaida!';
          alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
          alert.show = true;
        }
      } finally {
        data.loading = false;
      }
    };
    onMounted(() => {
      const loginAlert = store.state.alert;

      if (loginAlert.show) {
        alert.show = loginAlert.show;
        alert.type = loginAlert.type;
        alert.title = loginAlert.title;
        alert.text = loginAlert.text;
        alert.timeout = loginAlert.timeout;
        store.commit('resetAlert');
      }
    });
    return {
      data,
      errors,
      login,
      alert,
    };
  },
};
</script>
