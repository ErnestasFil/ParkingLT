<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon=" mdi-parking"></v-icon> &nbsp; Rezervacijos

          <v-spacer></v-spacer>

          <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Paieška" single-line flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table
          :loading="isLoading"
          v-model:search="search"
          :items="data.reservations"
          :headers="tableHeaders"
          items-per-page-text="Rezervacijų skaičius per puslapį"
          :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: -1, title: 'Visi' },
          ]"
          no-data-text="Rezervacijų nerasta, pagal jūsų nurodytą paiešką."
        >
          <template v-slot:loading>
            <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
          </template>
          <template v-slot:item="{ item }">
            <tr>
              <td>{{ item.date_from }}</td>
              <td>{{ item.date_until }}</td>
              <td>{{ item.price }} €</td>
              <td>{{ item.fk_Privilegeid }}</td>
              <td>
                <v-chip :color="getColor(item.isEnded)">
                  {{ item.isEnded }}
                </v-chip>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card>
      <ConfirmModal ref="confirmModalRef" />
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRoute, useRouter } from 'vue-router';
import store from '../plugins/store';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    ConfirmModal,
  },
  setup() {
    const toast = useToast();
    const confirmModalRef = ref(null);
    const route = useRoute();
    const router = useRouter();
    const zoneId = ref(route.params.parking_zone);
    const spaceId = ref(route.params.parking_space);
    const isLoading = ref(true);
    const data = reactive({
      zoneData: {},
      spaceData: {},
      reservations: [],
    });
    const tableHeaders = reactive([
      { title: 'Pradžios laikas', key: 'data_from' },
      { title: 'Pabaigos laikas', key: 'data_until' },
      { title: 'Kaina', key: 'price' },
      { title: 'Pritaikyta nuolaida', key: 'fk_Privilegeid' },
      { title: 'Rezervacija', key: 'isEnded' },
    ]);

    onMounted(async () => {
      try {
        await axios
          .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
            },
          })
          .then((response) => {
            if (response.status === 200) {
              data.zoneData = response.data;
              axios
                .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space/${spaceId.value}`, {
                  headers: {
                    'Content-Type': 'application/json',
                    Accept: '*/*',
                  },
                })
                .then((response) => {
                  if (response.status === 200) {
                    data.spaceData = response.data;
                    axios
                      .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space/${spaceId.value}/reservation`, {
                        headers: {
                          'Content-Type': 'application/json',
                          Accept: '*/*',
                          Authorization: `Bearer ${store.state.login.token}`,
                        },
                      })
                      .then((response) => {
                        if (response.status === 200) {
                          data.reservations = response.data.filter((reservation) => String(reservation.fk_Userid) === store.state.login.sub).sort((a, b) => new Date(b.date_until) - new Date(a.date_until));

                          data.reservations.forEach((reservation) => {
                            reservation.isEnded = new Date(reservation.date_until) < new Date() ? 'Pasibaigusi' : 'Galiojanti';
                            if (reservation.fk_Privilegeid) {
                              reservation.fk_Privilegeid = 'Taip';
                            } else {
                              reservation.fk_Privilegeid = 'Ne';
                            }
                          });
                        }
                      })
                      .catch((error) => {
                        if (error.response && error.response.status === 401) {
                          refresh.refreshToken(router).then(() => {
                            axios
                              .get(`${process.env.APP_URL}/parking_zone/${zoneId.value}/parking_space/${spaceId.value}/reservation`, {
                                headers: {
                                  'Content-Type': 'application/json',
                                  Accept: '*/*',
                                  Authorization: `Bearer ${store.state.login.token}`,
                                },
                              })
                              .then((response) => {
                                if (response.status === 200) {
                                  data.reservations = response.data.filter((reservation) => String(reservation.fk_Userid) === store.state.login.sub).sort((a, b) => new Date(b.date_until) - new Date(a.date_until));

                                  data.reservations.forEach((reservation) => {
                                    reservation.isEnded = new Date(reservation.date_until) < new Date() ? 'Pasibaigusi' : 'Galiojanti';
                                    if (reservation.fk_Privilegeid) {
                                      reservation.fk_Privilegeid = 'Taip';
                                    } else {
                                      reservation.fk_Privilegeid = 'Ne';
                                    }
                                  });
                                }
                              })
                              .catch((error) => {});
                          });
                        } else if (error.response && error.response.status === 403) {
                          toast.error('Prieiga negalima!', {
                            timeout: 10000,
                          });
                          router.push({ name: 'Home' });
                        } else if (error.response && error.response.status === 404) {
                          toast.error(error.response.data.message, {
                            timeout: 10000,
                          });
                          router.push({ name: 'Home' });
                        } else {
                          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
                            timeout: 10000,
                          });
                        }
                      });
                  }
                });
              setTimeout(() => {
                isLoading.value = false;
              }, 2000);
            }
          });
      } catch (error) {
        if (error.response && error.response.status === 403) {
          toast.error('Prieiga negalima!', {
            timeout: 10000,
          });
          router.push({ name: 'Home' });
        } else if (error.response && error.response.status === 404) {
          toast.error(error.response.data.message, {
            timeout: 10000,
          });
          router.push({ name: 'Home' });
        } else {
          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
            timeout: 10000,
          });
        }
      }
    });

    const getColor = (isEnded) => {
      return isEnded === 'Pasibaigusi' ? 'red' : 'green';
    };
    return {
      data,
      tableHeaders,
      confirmModalRef,
      isLoading,
      getColor,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>
