<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-card max-width="600" class="mx-auto text-center" title="Registracija">
        <v-form @submit.prevent="register">
          <v-text-field v-model="data.name" label="Vardas" :error-messages="errors.name"></v-text-field>
          <v-text-field v-model="data.surname" label="Pavardė" :error-messages="errors.surname"></v-text-field>
          <v-text-field v-model="data.email" label="El. paštas" :error-messages="errors.email"></v-text-field>
          <v-phone-input v-model="data.phone" @update:country="onCountryUpdate" guessCountry country-icon-mode="svg" label="Telefono numeris" countryLabel="Šalis" enable-searching-country invalidMessage="" :error-messages="errors.phone" />
          <v-text-field v-model="data.password" type="password" label="Slaptažodis" :error-messages="errors.password"></v-text-field>
          <v-text-field v-model="data.password_confirmation" type="password" label="Pakrtokite slaptažodį"></v-text-field>
          <v-btn :loading="data.loading" type="submit" block class="mt-2" text="Registruotis"></v-btn>
        </v-form>
      </v-card>
    </v-responsive>
  </v-container>
</template>
<script>
import { reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import 'flag-icons/css/flag-icons.min.css';
import 'v-phone-input/dist/v-phone-input.css';
import { VPhoneInput } from 'v-phone-input';
import { useToast } from 'vue-toastification';
export default {
  components: {
    VPhoneInput,
  },
  setup() {
    const toast = useToast();
    const data = reactive({ loading: false, name: '', surname: '', email: '', phone: '', phone_country: '', password: '', password_confirmation: '' });
    const errors = {};
    const router = useRouter();
    const register = async () => {
      try {
        data.loading = true;
        Object.keys(errors).forEach((key) => delete errors[key]);

        await axios
          .post(`${process.env.APP_URL}/register`, data, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
            },
          })
          .then((data) => {
            if (data.status === 201) {
              toast.success('Sveikiname prisiregistravus! Dabar galite prisijungti.', {
                timeout: 10000,
              });
              router.push({ name: 'Login' });
            }
          });
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const info = error.response.data;
          Object.assign(errors, info);
        } else {
          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
            timeout: 10000,
          });
        }
      } finally {
        data.loading = false;
      }
    };
    const onCountryUpdate = (countryIso) => {
      data.phone_country = countryIso;
    };
    return {
      data,
      register,
      errors,
      onCountryUpdate,
    };
  },
};
</script>
