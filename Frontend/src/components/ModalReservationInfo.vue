<template>
  <v-row justify="center">
    <v-dialog v-model="data.opened" min-width="600" max-width="900" @keydown.esc="cancel">
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Rezervacijos informacija</b> </span>
          </v-card-title>
        </v-toolbar>
        <template v-if="data.isLoading">
          <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
        </template>
        <template v-else>
          <v-card-text>
            <v-alert border="start" variant="tonal" title="Parkavimo vietos informacija"> {{ fullData.space.information }} </v-alert>
            <v-table class="pt-4 mx-lg-auto">
              <tbody>
                <tr>
                  <td>Zonos pavadinimas:</td>
                  <td>{{ fullData.zone.name }}</td>
                </tr>
                <tr>
                  <td>Miestas:</td>
                  <td>{{ fullData.zone.city }}</td>
                </tr>
                <tr>
                  <td>Gatvė:</td>
                  <td>{{ fullData.space.street }}</td>
                </tr>
                <tr>
                  <td>Stovėjimo vietos numeris:</td>
                  <td>{{ fullData.space.space_number }}</td>
                </tr>
                <tr>
                  <td>Neįgaliojo vieta:</td>
                  <td>{{ fullData.space.invalid_place ? 'Taip' : 'Ne' }}</td>
                </tr>
                <tr>
                  <td>Sumokėta suma:</td>
                  <td>{{ fullData.reservation.price }} €</td>
                </tr>
                <tr>
                  <td>Pradžios laikas:</td>
                  <td>{{ fullData.reservation.date_from }}</td>
                </tr>
                <tr>
                  <td>Pabaigos laikas:</td>
                  <td>{{ fullData.reservation.date_until }}</td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
          <v-btn block outlined class="flex-grow-1" variant="tonal" @click.native="openZone"> Atidaryti parkavimo zoną </v-btn>
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
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
export default {
  setup() {
    const toast = useToast();
    const data = reactive({ opened: false, reservationId: 0, spaceId: 0, zoneId: 0, resolve: null, reject: null, isLoading: true });
    const router = useRouter();
    const fullData = reactive({ zone: {}, space: {}, reservation: {} });
    const open = (reservationId, spaceId, zoneId) => {
      data.isLoading = true;
      data.reservationId = reservationId;
      data.zoneId = zoneId;
      data.spaceId = spaceId;
      data.opened = true;
      try {
        axios
          .get(`${process.env.APP_URL}/parking_zone/${data.zoneId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
            },
          })
          .then((parkingZone) => {
            if (parkingZone.status === 200) {
              fullData.zone = parkingZone.data;
              axios
                .get(`${process.env.APP_URL}/parking_zone/${data.zoneId}/parking_space/${data.spaceId}`, {
                  headers: {
                    'Content-Type': 'application/json',
                    Accept: '*/*',
                  },
                })
                .then((parkingSpace) => {
                  if (parkingSpace.status === 200) {
                    fullData.space = parkingSpace.data;
                    axios
                      .get(`${process.env.APP_URL}/parking_zone/${data.zoneId}/parking_space/${data.spaceId}/reservation/${data.reservationId}`, {
                        headers: {
                          'Content-Type': 'application/json',
                          Accept: '*/*',
                          Authorization: `Bearer ${store.state.login.token}`,
                        },
                      })
                      .then((reserv) => {
                        if (reserv.status === 200) {
                          fullData.reservation = reserv.data;
                          fullData.reservation.price = Number(fullData.reservation.price).toFixed(2);
                        }
                      })
                      .catch((error) => {
                        if (error.response && error.response.status === 401) {
                          refresh.refreshToken(router).then(() => {
                            axios
                              .get(`${process.env.APP_URL}/parking_zone/${data.zoneId}/parking_space/${data.spaceId}/reservation/${data.reservationId}`, {
                                headers: {
                                  'Content-Type': 'application/json',
                                  Accept: '*/*',
                                  Authorization: `Bearer ${store.state.login.token}`,
                                },
                              })
                              .then((reserv) => {
                                if (reserv.status === 200) {
                                  fullData.reservation = reserv.data;
                                  fullData.reservation.price = Number(fullData.reservation.price).toFixed(2);
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
            }
          });
        setTimeout(() => {
          data.isLoading = false;
        }, 1500);
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
      return new Promise((resolve, reject) => {
        data.resolve = resolve;
        data.reject = reject;
      });
    };
    const cancel = () => {
      data.resolve(false);
      data.opened = false;
    };
    const openZone = () => {
      data.resolve(false);
      data.opened = false;
      router.push({ name: 'ParkingSpace', params: { id: data.zoneId } });
    };
    return { data, fullData, openZone, open, cancel };
  },
};
</script>
