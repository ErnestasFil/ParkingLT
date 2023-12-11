<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>

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
              <td>{{ item.fk_UserEmail }}</td>
              <td>{{ item.date_from }}</td>
              <td>{{ item.date_until }}</td>
              <td>{{ item.price }} €</td>
              <td>{{ item.fk_Privilegeid }}</td>
              <td>
                <v-chip :color="getColor(item.isEnded)">
                  {{ item.isEnded }}
                </v-chip>
              </td>
              <td>
                <v-btn prepend-icon="mdi-note-edit" :disabled="item.isEnded !== 'Galiojanti'" color="" class="mr-3" @click="editItem(item.id, item.fk_Parking_spaceid, item.fk_Parking_zoneid)">
                  <template v-slot:prepend>
                    <v-icon :color="item.isEnded === 'Galiojanti' ? 'yellow' : 'darkgrey'"></v-icon>
                  </template>
                  Redagavimas
                </v-btn>
              </td>
              <td>
                <v-btn prepend-icon="mdi-delete-forever-outline" color="" class="mr-3" @click="deleteInfo(item.id, item.fk_Parking_spaceid, item.fk_Parking_zoneid)">
                  <template v-slot:prepend>
                    <v-icon color="red"></v-icon>
                  </template>
                  Šalinimas
                </v-btn>
              </td>
              <td>
                <v-btn prepend-icon="mdi-information-outline" color="" class="mr-3" @click="openInfo(item.id, item.fk_Parking_spaceid, item.fk_Parking_zoneid, item.fk_UserEmail)">
                  <template v-slot:prepend>
                    <v-icon color="white"></v-icon>
                  </template>
                  Informacija
                </v-btn>
              </td>
            </tr>
          </template>
        </v-data-table>
      </v-card>
    </v-responsive>
  </v-container>
  <InfoModal ref="informationRef" />
  <EditModal ref="editRef" />
  <ConfirmModal ref="confirmModalRef" />
</template>

<script>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import AlertComponent from '../components/Alert.vue';
import InfoModal from '../components/ModalUserReservationInfo.vue';
import EditModal from '../components/EditReservation.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRoute } from 'vue-router';
import store from '../plugins/store';
export default {
  components: {
    AlertComponent,
    InfoModal,
    EditModal,
    ConfirmModal,
  },
  setup() {
    const confirmModalRef = ref(null);
    const informationRef = ref(null);
    const editRef = ref(null);
    const route = useRoute();
    const userId = ref(route.params.id);
    const isLoading = ref(true);
    const data = reactive({
      reservations: [],
    });
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });
    const tableHeaders = reactive([
      { title: 'Vartotojo el. paštas', key: 'fk_UserEmail' },
      { title: 'Pradžios laikas', key: 'data_from' },
      { title: 'Pabaigos laikas', key: 'data_until' },
      { title: 'Kaina', key: 'price' },
      { title: 'Pritaikyta nuolaida', key: 'fk_Privilegeid' },
      { title: 'Rezervacija', key: 'isEnded' },
      { title: '', key: '' },
      { title: '', key: '' },
      { title: '', key: '' },
    ]);

    onMounted(async () => {
      alert.show = false;
      try {
        axios
          .get(`${process.env.APP_URL}/user/${userId.value}/reservation`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((response) => {
            if (response.status === 200) {
              data.reservations = response.data.sort((a, b) => new Date(b.date_until) - new Date(a.date_until));
              data.reservations.forEach((reservation) => {
                reservation.isEnded = new Date(reservation.date_until) < new Date() ? 'Pasibaigusi' : 'Galiojanti';
                if (reservation.fk_Privilegeid) {
                  reservation.fk_Privilegeid = 'Taip';
                } else {
                  reservation.fk_Privilegeid = 'Ne';
                }
              });
              console.log(data);
            }
          });
        setTimeout(() => {
          isLoading.value = false;
        }, 2000);
      } catch (error) {
        console.log(error);
        alert.show = true;
        alert.type = 'error';
        alert.title = 'Klaida!';
        alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
        alert.timeout = 10000;
      }
    });
    const editItem = async (reservationId, spaceId, zoneId) => {
      const confirmation = await editRef.value.open(reservationId, spaceId, zoneId);
      if (confirmation) {
        const index = data.reservations.findIndex((reservation) => reservation.id === reservationId);

        if (index !== -1) {
          data.reservations[index].date_until = confirmation.until;
          data.reservations[index].price = confirmation.price;
        }
        console.log(confirmation);
      }
    };
    const openInfo = (reservationId, spaceId, zoneId, user) => {
      informationRef.value.open(reservationId, spaceId, zoneId, user);
      console.log(`Info ${reservationId}`);
    };
    const deleteInfo = async (reservationId, spaceId, zoneId) => {
      const confirmation = await confirmModalRef.value.open('Rezervacijos šalinimas', 'Ar tikrai norite pašalinti šią reservaziją?');
      if (confirmation) {
        try {
          console.log(store.state.login.token);
          const response = await axios.delete(`${process.env.APP_URL}/parking_zone/${zoneId}/parking_space/${spaceId}/reservation/${reservationId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          });

          if (response.status === 204) {
            const alert = {
              show: true,
              type: 'success',
              title: 'Pašalinta!',
              text: 'Rezervacija pašalinta sėkmingai!',
              timeout: 10000,
            };
            store.commit('setAlert', alert);

            data.reservations = data.reservations.filter((reserv) => reserv.id !== reservationId);
          }
        } catch (error) {
          console.log(error);
          const alert = {
            show: true,
            type: 'error',
            title: 'Šalinimo klaida!',
            text: error.response ? error.response.data.message : 'Nenumatyta klaida',
            timeout: 10000,
          };
          store.commit('setAlert', alert);
        }
      }
    };

    const getColor = (isEnded) => {
      return isEnded === 'Pasibaigusi' ? 'red' : 'green';
    };
    onMounted(() => {
      updateAlert();
      watch(() => store.state.alert, updateAlert);
    });

    const updateAlert = () => {
      const loginAlert = store.state.alert;

      if (loginAlert.show) {
        alert.show = loginAlert.show;
        alert.type = loginAlert.type;
        alert.title = loginAlert.title;
        alert.text = loginAlert.text;
        alert.timeout = loginAlert.timeout;
        store.commit('resetAlert');
      }
    };
    return {
      alert,
      data,
      tableHeaders,
      informationRef,
      confirmModalRef,
      editRef,
      isLoading,
      getColor,
      openInfo,
      editItem,
      deleteInfo,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>