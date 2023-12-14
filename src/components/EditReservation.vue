<template>
  <v-row justify="center">
    <v-dialog v-model="data.opened" max-width="900" @keydown.esc="cancel">
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text"> <span class="mdi mdi-map"></span> <b>Rezervacijos redagavimas</b> </span>
          </v-card-title>
        </v-toolbar>
        <template v-if="data.isLoading">
          <v-skeleton-loader type="table-row@10"></v-skeleton-loader>
        </template>
        <template v-else>
          <v-row no-gutters>
            <v-col cols="12" xl="6">
              <v-sheet class="pa-2 ma-2"
                ><v-card-text>
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
                        <td>Apmokestinamas laikas:</td>
                        <td>{{ fullData.zone.paying_time }} min</td>
                      </tr>
                      <tr>
                        <td>Kaina:</td>
                        <td>{{ typeof fullData.zone.price !== 'undefined' ? fullData.zone.price.toFixed(2) + ' €' : 'N/A' }}</td>
                      </tr>
                      <tr>
                        <td>Sumokėta suma:</td>
                        <td>{{ fullData.reservation.price }} €</td>
                      </tr>
                    </tbody>
                  </v-table>
                </v-card-text>
              </v-sheet>
            </v-col>

            <v-col cols="12" xl="6">
              <v-sheet class="pa-2 ma-2">
                <v-card-text>
                  <v-alert border="start" variant="tonal" type="success" title="Laiko keitimas" class="mb-10 mx-lg-auto"> </v-alert>
                  <v-table class="mx-lg-auto">
                    <tbody>
                      <tr>
                        <td>Pradžios laikas:</td>
                        <td>{{ fullData.reservation.date_from }}</td>
                      </tr>
                      <tr>
                        <td>Pabaigos laikas:</td>
                        <td>{{ timeSelect.endTime }}</td>
                      </tr>
                      <tr>
                        <td>Mokama suma:</td>
                        <td>{{ timeSelect.price.toFixed(2) }} €</td>
                      </tr>
                    </tbody>
                  </v-table>
                  <v-select v-model="timeSelect.selectedTime" class="mt-4" label="Pasirinkite rezervacijos trukmę" :items="timeSelect.timeOptions"></v-select>
                </v-card-text>
                <v-btn block outlined class="flex-grow-1" variant="tonal" @click.native="updateReservation"> Atnaujinti rezervaciją </v-btn>
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
import { watch, onMounted, reactive } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
import { useRouter } from 'vue-router';
export default {
  setup() {
    const router = useRouter();
    const toast = useToast();
    const data = reactive({ opened: false, reservationId: 0, spaceId: 0, zoneId: 0, resolve: null, reject: null, isLoading: true });
    const fullData = reactive({ zone: {}, space: {}, reservation: {} });
    const timeSelect = reactive({
      selectedTime: null,
      timeOptions: [],
      endTime: new Date(),
      price: 0,
      options: {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
        timeZone: 'Europe/Vilnius',
      },
    });
    const open = (reservationId, spaceId, zoneId) => {
      data.isLoading = true;
      data.reservationId = reservationId;
      data.zoneId = zoneId;
      data.spaceId = spaceId;
      data.opened = true;
      timeSelect.price = 0;
      timeSelect.endTime = new Date();
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
                        timeSelect.endTime = fullData.reservation.date_until;
                        fullData.reservation.price = Number(fullData.reservation.price).toFixed(2);
                        generateTimeOptions(fullData.zone.paying_time);
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
                                timeSelect.endTime = fullData.reservation.date_until;
                                fullData.reservation.price = Number(fullData.reservation.price).toFixed(2);
                                generateTimeOptions(fullData.zone.paying_time);
                              }
                            })
                            .catch((error) => {
                              refresh.error403(error, router);
                              refresh.error404(error, router);
                              refresh.errorOther(error, router);
                            });
                        });
                      } else {
                        refresh.error403(error, router);
                        refresh.error404(error, router);
                        refresh.errorOther(error, router);
                      }
                    });
                }
              })
              .catch((error) => {
                refresh.error403(error, router);
                refresh.error404(error, router);
                refresh.errorOther(error, router);
              });
          }
        })
        .catch((error) => {
          refresh.error403(error, router);
          refresh.error404(error, router);
          refresh.errorOther(error, router);
        });
      setTimeout(() => {
        data.isLoading = false;
      }, 1500);

      return new Promise((resolve, reject) => {
        data.resolve = resolve;
        data.reject = reject;
      });
    };
    const cancel = () => {
      data.resolve(false);
      data.opened = false;
    };

    const updateReservation = async () => {
      const updatedData = reactive({ price: 0, until: null });
      const [selectedHours, selectedMinutes] = timeSelect.selectedTime.split(':').map(Number);
      const time = selectedHours * 60 + selectedMinutes;
      await axios
        .patch(
          `${process.env.APP_URL}/parking_zone/${data.zoneId}/parking_space/${data.spaceId}/reservation/${data.reservationId}`,
          { time: time },
          {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          }
        )
        .then((response) => {
          if (response.status === 200) {
            updatedData.price = response.data.price;
            updatedData.until = response.data.date_until;
            toast.success('Rezervacijos informacija atnaujinta!', {
              timeout: 10000,
            });
            data.resolve(updatedData);
            data.opened = false;
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 422) {
            toast.error(error.response.data.time[0], {
              timeout: 10000,
            });
          } else if (error.response && error.response.status === 401) {
            refresh.refreshToken(router).then(() => {
              axios
                .patch(
                  `${process.env.APP_URL}/parking_zone/${data.zoneId}/parking_space/${data.spaceId}/reservation/${data.reservationId}`,
                  { time: time },
                  {
                    headers: {
                      'Content-Type': 'application/json',
                      Accept: '*/*',
                      Authorization: `Bearer ${store.state.login.token}`,
                    },
                  }
                )
                .then((response) => {
                  if (response.status === 200) {
                    updatedData.price = response.data.price;
                    updatedData.until = response.data.date_until;
                    toast.success('Rezervacijos informacija atnaujinta!', {
                      timeout: 10000,
                    });
                    data.resolve(updatedData);
                    data.opened = false;
                  }
                })
                .catch((error) => {
                  if (error.response && error.response.status === 422) {
                    toast.error(error.response.data.time[0], {
                      timeout: 10000,
                    });
                  } else {
                    refresh.error403(error, router);
                    refresh.error404(error, router);
                    refresh.errorOther(error, router);
                  }
                });
            });
          } else {
            refresh.error403(error, router);
            refresh.error404(error, router);
            refresh.errorOther(error, router);
          }
          data.resolve(false);
          data.opened = false;
        });
    };
    const generateTimeOptions = (payingTime) => {
      const startDate = new Date(fullData.reservation.date_from);
      const endDate = new Date(fullData.reservation.date_until);
      const timeDifference = endDate - startDate;
      const mins = Math.floor(timeDifference / (1000 * 60));
      timeSelect.timeOptions = [];
      const maxDuration = 24 * 60;
      for (let i = mins; i <= maxDuration; i += payingTime) {
        const hours = Math.floor(i / 60);
        const minutes = i % 60;
        const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        timeSelect.timeOptions.push(formattedTime);
      }
      timeSelect.selectedTime = timeSelect.timeOptions[0];
    };
    onMounted(() => {
      watch(
        () => timeSelect.selectedTime,
        () => {
          if (fullData.reservation) {
            const [selectedHours, selectedMinutes] = timeSelect.selectedTime.split(':').map(Number);
            const newDateTime = new Date(fullData.reservation.date_from);
            newDateTime.setHours(newDateTime.getHours() + selectedHours, newDateTime.getMinutes() + selectedMinutes);
            timeSelect.endTime = new Intl.DateTimeFormat('lt-LT', timeSelect.options).format(newDateTime);
            timeSelect.price = Math.abs(((selectedHours * 60 + selectedMinutes) / fullData.zone.paying_time) * fullData.zone.price - fullData.reservation.price);
          }
        }
      );
    });

    return { data, fullData, timeSelect, updateReservation, open, cancel };
  },
};
</script>
