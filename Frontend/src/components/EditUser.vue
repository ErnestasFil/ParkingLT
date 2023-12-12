<template>
  <v-row justify="center">
    <v-dialog v-model="data.opened" min-width="600" max-width="900" @keydown.esc="cancel">
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text-h5"> <span class="mdi mdi-account-key"></span> <b>Vartotojo informacijos redagavimas</b> </span>
          </v-card-title>
        </v-toolbar>
        <template v-if="data.isLoading">
          <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
        </template>
        <template v-else>
          <v-row no-gutters>
            <v-col cols="6">
              <v-sheet class="pa-2 ma-2"
                ><v-card-text>
                  <v-alert border="start" variant="tonal" title="Vartotojo informacija"></v-alert>
                  <v-table class="pt-4 mx-lg-auto">
                    <tbody>
                      <tr>
                        <td>Vardas:</td>
                        <td>{{ fullData.user.name }}</td>
                      </tr>
                      <tr>
                        <td>Pavardė:</td>
                        <td>{{ fullData.user.surname }}</td>
                      </tr>
                      <tr>
                        <td>El paštas:</td>
                        <td>{{ fullData.user.email }}</td>
                      </tr>
                      <tr>
                        <td>Telefono numeris:</td>
                        <td>{{ fullData.user.phone }}</td>
                      </tr>
                      <tr>
                        <td>Rolė:</td>
                        <td>{{ fullData.user.role }}</td>
                      </tr>
                      <tr>
                        <td>Balansas:</td>
                        <td>{{ fullData.user.balance }} €</td>
                      </tr>
                    </tbody>
                  </v-table>
                </v-card-text>
              </v-sheet>
            </v-col>

            <v-col cols="6">
              <v-sheet class="pa-2 ma-2">
                <v-form @submit.prevent="edit">
                  <v-card-text>
                    <v-alert border="start" variant="tonal" type="success" title="Informacijos keitimas" class="mb-10 mx-lg-auto"> </v-alert>
                    <v-table class="mx-lg-auto">
                      <v-text-field v-model="dataSend.name" label="Vardas" :error-messages="errors.name"></v-text-field>
                      <v-text-field v-model="dataSend.surname" label="Pavardė" :error-messages="errors.surname"></v-text-field>
                      <v-phone-input
                        v-model="dataSend.phone"
                        @update:country="onCountryUpdate"
                        guessCountry
                        country-icon-mode="svg"
                        label="Telefono numeris"
                        countryLabel="Šalis"
                        enable-searching-country
                        invalidMessage=""
                        :error-messages="errors.phone"
                      />
                    </v-table>
                  </v-card-text>
                  <v-btn block outlined :loading="data.loading" type="submit" class="flex-grow-1" variant="tonal"> Atnaujinti rezervaciją </v-btn>
                </v-form>
              </v-sheet>
            </v-col>
          </v-row>
        </template>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click.native="cancel"> Uždaryti </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
import store from '../plugins/store';
import { reactive } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
export default {
  setup() {
    const toast = useToast();
    const data = reactive({
      opened: false,
      userId: 0,
      resolve: null,
      reject: null,
      isLoading: true,
      loading: false,
    });
    const dataSend = reactive({
      name: '',
      surname: '',
      phone: null,
      phone_country: '',
    });
    const fullData = reactive({ user: {} });
    let errors = {};
    const open = (userId) => {
      data.isLoading = true;
      data.loading = false;
      data.userId = userId;
      data.opened = true;
      dataSend.name = '';
      dataSend.surname = '';
      dataSend.phone = '';
      try {
        axios
          .get(`${process.env.APP_URL}/user/${data.userId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((dataUser) => {
            if (dataUser.status === 200) {
              fullData.user = dataUser.data;
              if (fullData.user.role && fullData.user.role === 'User') {
                fullData.user.role = 'Vartotojas';
              } else if (fullData.user.role && fullData.user.role === 'Administrator') {
                fullData.user.role = 'Administratorius';
              } else {
                fullData.user.role = 'Nepatvirtintas vartotojas';
              }
              dataSend.name = fullData.user.name;
              dataSend.surname = fullData.user.surname;
              dataSend.phone = fullData.user.phone;
            }
          });
        setTimeout(() => {
          data.isLoading = false;
        }, 1500);
      } catch (error) {
        toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
          timeout: 10000,
        });
      }
      return new Promise((resolve, reject) => {
        data.resolve = resolve;
        data.reject = reject;
      });
    };
    const cancel = () => {
      data.resolve(false);
      data.opened = false;
    };
    const onCountryUpdate = (countryIso) => {
      dataSend.phone_country = countryIso;
    };
    const edit = async () => {
      try {
        data.loading = true;
        Object.keys(errors).forEach((key) => delete errors[key]);

        await axios
          .put(`${process.env.APP_URL}/user/${data.userId}`, dataSend, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((dataUs) => {
            if (dataUs.status === 200) {
              toast.success('Vartotojo informacija atnaujinta!', {
                timeout: 10000,
              });
              data.resolve(dataSend);
              data.opened = false;
            }
          });
      } catch (error) {
        console.log(error);
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

    return { data, dataSend, fullData, errors, onCountryUpdate, open, cancel, edit };
  },
};
</script>
