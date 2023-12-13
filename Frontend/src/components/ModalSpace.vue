<template>
  <v-row justify="center">
    <v-dialog v-model="isModalOpen" max-width="600" persistent>
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text"> <span class="mdi mdi-map"></span> <b>Parkavimosi vieta</b> </span>
          </v-card-title>
        </v-toolbar>
        <v-card-text>
          <v-alert border="start" variant="tonal" title="Parkavimo vietos informacija">{{ fullData.information }} </v-alert>
          <v-table height="500px" class="pt-4 mx-lg-auto">
            <tbody>
              <tr>
                <td>Zonos pavadinimas:</td>
                <td>{{ zoneData.name }}</td>
              </tr>
              <tr>
                <td>Miestas:</td>
                <td>{{ zoneData.city }}</td>
              </tr>
              <tr>
                <td>Gatvė:</td>
                <td>{{ fullData.street }}</td>
              </tr>
              <tr>
                <td>Stovėjimo vietos numeris:</td>
                <td>{{ fullData.space_number }}</td>
              </tr>
              <tr v-if="isAuthenticated">
                <td>Užimtumas:</td>
                <td>{{ spaceFree ? 'Laisva' : 'Užimta' }}</td>
              </tr>
              <tr>
                <td>Neįgaliojo vieta:</td>
                <td>{{ fullData.invalid_place ? 'Taip' : 'Ne' }}</td>
              </tr>
              <tr>
                <td>Apmokestinamas laikas:</td>
                <td>{{ zoneData.paying_time }} min</td>
              </tr>
              <tr>
                <td>Kaina:</td>
                <td>{{ typeof zoneData.price !== 'undefined' ? zoneData.price.toFixed(2) + ' €' : 'N/A' }}</td>
              </tr>
              <tr v-if="isAdmin">
                <td>
                  <v-btn prepend-icon="mdi-file-edit-outline" @click="edit(zoneData.id, fullData.id)">
                    <template v-slot:prepend>
                      <v-icon color="warning"></v-icon>
                    </template>
                    Redaguoti
                  </v-btn>
                </td>
                <td>
                  <v-btn prepend-icon="mdi-delete-empty-outline" @click="deleteSpace(zoneData.id, fullData.id)">
                    <template v-slot:prepend>
                      <v-icon color="error"></v-icon>
                    </template>
                    Pašalinti
                  </v-btn>
                </td>
              </tr>
            </tbody>
          </v-table>
          <v-alert border="start" v-if="!isAuthenticated" variant="tonal">Norėdami rezervuoti vietą prašome prisijungti</v-alert>
        </v-card-text>
        <v-btn v-if="isAuthenticated" block outlined class="flex-grow-1 mb-3" variant="tonal" @click="reservations(zoneData.id, fullData.id)"> Mano rezervacijos šioje vietoje </v-btn>
        <v-btn v-if="isAuthenticated && spaceFree" block outlined class="flex-grow-1" variant="tonal" @click="makeReservation(zoneData, fullData)"> Rezervuoti stovėjimo vietą </v-btn>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeModal"> Uždaryti </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
  <ModalReservation ref="confirmModalRef" @sendData="dataReceived" />
</template>

<script>
import store from '../plugins/store';
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import ModalReservation from './AddReservation.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import refresh from '../plugins/refreshToken';
export default {
  components: {
    ModalReservation,
  },
  props: {
    show: Boolean,
    fullData: Object,
    zoneData: Object,
    spaceFree: Boolean,
  },
  emits: ['modalClosed', 'deleteSpace', 'update:show'],
  data() {
    return {
      isModalOpen: false,
      showReservation: false,
    };
  },
  watch: {
    show(newVal) {
      this.isModalOpen = newVal;
    },
  },
  methods: {
    closeModal() {
      this.$emit('update:show', false);
      this.isModalOpen = false;
      this.$emit('modalClosed');
    },
    edit(zone, space) {
      this.router.push({ name: 'EditParkingSpace', params: { parking_zone: zone, parking_space: space } });
    },
    deleteSpace(zone, space) {
      this.$emit('update:show', false);
      this.isModalOpen = false;
      this.$emit('modalClosed');
      this.$emit('deleteSpace', zone, space);
    },
    reservations(zone, space) {
      this.router.push({ name: 'Reservation', params: { parking_zone: zone, parking_space: space } });
    },
  },
  setup(props, { emit }) {
    const toast = useToast();
    const confirmModalRef = ref(null);
    let time = 0;
    const isAuthenticated = computed(() => {
      return store.getters.isAuthenticated;
    });
    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const router = useRouter();
    const dataReceived = (data) => {
      time = data;
    };
    const makeReservation = async (zone, space) => {
      emit('update:show', false);
      emit('modalClosed');
      const confirmation = await confirmModalRef.value.open(zone, space);
      if (confirmation) {
        await axios
          .post(
            `${process.env.APP_URL}/parking_zone/${zone.id}/parking_space/${space.id}/reservation`,
            { time },
            {
              headers: {
                'Content-Type': 'application/json',
                Accept: '*/*',
                Authorization: `Bearer ${store.state.login.token}`,
              },
            }
          )
          .then((data) => {
            if (data.status === 201) {
              toast.success('Rezervacija pridėta!', {
                timeout: 10000,
              });
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
                  .post(
                    `${process.env.APP_URL}/parking_zone/${zone.id}/parking_space/${space.id}/reservation`,
                    { time },
                    {
                      headers: {
                        'Content-Type': 'application/json',
                        Accept: '*/*',
                        Authorization: `Bearer ${store.state.login.token}`,
                      },
                    }
                  )
                  .then((data) => {
                    if (data.status === 201) {
                      toast.success('Rezervacija pridėta!', {
                        timeout: 10000,
                      });
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
          });
      }
    };
    return {
      makeReservation,
      dataReceived,
      confirmModalRef,
      isAuthenticated,
      isAdmin,
      router,
    };
  },
};
</script>
