<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <AlertComponent :show="alert.show" :type="alert.type" :title="alert.title" :text="alert.text" :timeout="alert.timeout"></AlertComponent>

      <v-snackbar v-model="alert.show" :timeout="alert.timeout" variant="plain" origin="auto" position="fixed" class="mb-6 mx-auto" max-width="auto"> </v-snackbar>
      <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
          <v-icon icon="mdi-map-marker-question-outline"></v-icon> &nbsp; Parkavimo zonos paieška

          <v-spacer></v-spacer>

          <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Paieška" single-line flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table
          v-model:search="search"
          :items="data.zoneData"
          :headers="tableHeaders"
          items-per-page-text="Parkavimo zonų per puslapį"
          :items-per-page-options="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
            { value: -1, title: 'Visi' },
          ]"
          no-data-text="Parkavimosi zonų nerasta, pagal jūsų nurodytą paiešką."
        >
          <template v-slot:item="{ item }">
            <tr>
              <td>{{ item.name }}</td>
              <td>{{ item.city }}</td>
              <td>{{ item.paying_time }} min</td>
              <td>{{ item.price.toFixed(2) }} €</td>
              <td>{{ item.information }}</td>
              <td>
                <v-btn @click="goToZone(item.id)" color="primary"> Atidaryti zoną </v-btn>
              </td>
              <td v-if="isAdmin">
                <v-btn density="comfortable" color="yellow" icon="mdi-note-edit" class="mr-3" @click="editItem(item.id)"></v-btn><v-btn density="comfortable" color="red" icon="mdi-delete-forever" @click="deleteItem(item.id, item.name)"></v-btn>
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
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import AlertComponent from '../components/Alert.vue';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useRouter } from 'vue-router';
import store from '../plugins/store';
export default {
  components: {
    AlertComponent,
    ConfirmModal,
  },
  setup() {
    const confirmModalRef = ref(null);
    const data = reactive({
      zoneData: [],
    });
    const alert = reactive({
      show: false,
      type: 'error',
      title: '',
      text: '',
      timeout: 10000,
    });
    const router = useRouter();
    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const tableHeaders = reactive([
      { title: 'Zonos pavadinimas', key: 'name' },
      { title: 'Miestas', key: 'city' },
      { title: 'Apmokestinamas laikas', key: 'paying_time' },
      { title: 'Kaina', key: 'price' },
      { title: 'Informacija', key: 'information' },
      { title: '', key: '' },
      ...(isAdmin.value ? [{ title: 'Admin meniu', key: '' }] : []),
    ]);
    onMounted(async () => {
      try {
        const response = await axios.get(`${process.env.APP_URL}/parking_zone`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        });

        if (response.status === 200) {
          data.zoneData = response.data;
          console.log(data.zoneData);
        }
      } catch (error) {
        alert.show = true;
        alert.type = 'error';
        alert.title = 'Klaida!';
        alert.text = error.response ? error.response.data.message : 'Nenumatyta klaida';
        alert.timeout = 10000;
      }
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

    return {
      alert,
      data,
      tableHeaders,
      goToZone,
      deleteItem,
      confirmModalRef,
      editItem,
      isAdmin,
    };
  },

  data() {
    return {
      search: '',
    };
  },
};
</script>
