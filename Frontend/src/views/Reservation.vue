<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>

      <v-row no-gutters>
        <v-col cols="9">
          <v-sheet class="pa-2 ma-2">
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
                    <td v-if="isAuthenticated">
                      <v-btn :disabled="item.isEnded !== 'Galiojanti'" density="comfortable" :color="item.isEnded === 'Galiojanti' ? 'yellow' : 'darkgrey'" icon="mdi-note-edit" class="mr-3" @click="editItem(item.id)" />
                    </td>
                  </tr>
                </template>
              </v-data-table>
            </v-card>
          </v-sheet>
        </v-col>

        <v-col>
          <v-sheet class="pa-2 ma-2">
            <v-toolbar>
              <v-card-title>
                <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Rezervazijų informacija</b> </span>
              </v-card-title>
            </v-toolbar>
            <v-card-text>
              <v-alert border="start" variant="tonal" title="Parkavimo zonos informacija"> {{ data.zoneData.information }}</v-alert>
              <v-table class="pt-4 mx-lg-auto">
                <tbody>
                  <tr>
                    <td>Zonos pavadinimas:</td>
                    <td>{{ data.zoneData.name }}</td>
                  </tr>
                  <tr>
                    <td>Miestas:</td>
                    <td>{{ data.zoneData.city }}</td>
                  </tr>
                  <tr>
                    <td>Gatvė:</td>
                    <td>{{ data.spaceData.street }}</td>
                  </tr>
                  <tr>
                    <td>Stovėjimo vietos numeris:</td>
                    <td>{{ data.spaceData.space_number }}</td>
                  </tr>
                  <tr>
                    <td>Neįgaliojo vieta:</td>
                    <td>{{ data.spaceData.invalid_place ? 'Taip' : 'Ne' }}</td>
                  </tr>
                  <tr>
                    <td>Apmokestinamas laikas:</td>
                    <td>{{ data.zoneData.paying_time }} min</td>
                  </tr>
                  <tr>
                    <td>Kaina:</td>
                    <td>{{ typeof data.zoneData.price !== 'undefined' ? data.zoneData.price.toFixed(2) + ' €' : 'N/A' }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card-text>
            <v-btn v-if="isAuthenticated" block outlined class="flex-grow-1" variant="tonal" @click="openZone"> Atidaryti parkavimosi zoną</v-btn>
          </v-sheet>
        </v-col>
      </v-row>
      <ConfirmModal ref="confirmModalRef" />
    </v-responsive>
  </v-container>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import AlertComponent from '../components/Alert.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRouter, useRoute } from 'vue-router';
import store from '../plugins/store';
export default {
  components: {
    AlertComponent,
    ConfirmModal,
  },
  setup() {
    const confirmModalRef = ref(null);
    const router = useRouter();
    const route = useRoute();
    const zoneId = ref(route.params.parking_zone);
    const spaceId = ref(route.params.parking_space);
    const isLoading = ref(true);
    const data = reactive({
      zoneData: {},
      spaceData: {},
      reservations: [],
    });
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });

    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const isAuthenticated = computed(() => {
      return store.getters.isAuthenticated;
    });
    const tableHeaders = reactive([
      { title: 'Pradžios laikas', key: 'data_from' },
      { title: 'Pabaigos laikas', key: 'data_until' },
      { title: 'Kaina', key: 'price' },
      { title: 'Pritaikyta nuolaida', key: 'fk_Privilegeid' },
      { title: 'Rezervacija', key: 'isEnded' },
      ...(isAuthenticated.value ? [{ title: 'Rezervacijos valdymas', key: '' }] : []),
    ]);

    onMounted(async () => {
      alert.show = false;
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
                        console.log(error);
                        alert.show = true;
                        alert.type = 'error';
                        alert.title = 'Klaida!';
                        alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
                        alert.timeout = 10000;
                      });
                  }
                });
              console.log(data);
              setTimeout(() => {
                isLoading.value = false;
              }, 2000);
            }
          });
      } catch (error) {
        console.log(error);
        alert.show = true;
        alert.type = 'error';
        alert.title = 'Klaida!';
        alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
        alert.timeout = 10000;
      }
      console.log(alert);
    });

    const goToZone = (zoneId) => {
      router.push({ name: 'ParkingSpace', params: { id: zoneId } });
      console.log(`Navigating to Zone ${zoneId}`);
    };
    const deleteItem = async (zoneId, name) => {
      const confirmation = await confirmModalRef.value.open('Parkavimo zonos pašalinimas', 'Ar tikrai norite pašalinti parkavimosi zoną - ' + name + ' ir visą su ja susijusią informaciją?');
      if (confirmation) {
        alert.show = false;
        try {
          console.log(store.state.login.token);
          const response = await axios.delete(`${process.env.APP_URL}/parking_zone/${zoneId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          });

          if (response.status === 204) {
            alert.show = true;
            alert.type = 'success';
            alert.title = 'Pašalinta!';
            alert.text = 'Parkavimosi zona pašalinta sėkmingai!';
            alert.timeout = 10000;
            data.zoneData = data.zoneData.filter((item) => item.id !== zoneId);
          }
        } catch (error) {
          console.log(error);
          alert.show = true;
          alert.type = 'error';
          alert.title = 'Šalinimo klaida!';
          alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
          alert.timeout = 10000;
        }
      }
    };

    const editItem = (zoneId) => {
      router.push({ name: 'EditParkingZone', params: { id: zoneId } });
      console.log(`Edit ${zoneId}`);
    };
    const openZone = () => {
      router.push({ name: 'ParkingSpace', params: { id: zoneId.value } });
      console.log(`Edit ${zoneId}`);
    };
    const getColor = (isEnded) => {
      return isEnded === 'Pasibaigusi' ? 'red' : 'green';
    };
    return {
      alert,
      data,
      tableHeaders,
      goToZone,
      deleteItem,
      confirmModalRef,
      editItem,
      isAdmin,
      isAuthenticated,
      isLoading,
      openZone,
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
