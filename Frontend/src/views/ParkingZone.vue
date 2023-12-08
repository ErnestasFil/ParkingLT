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
            </tr>
          </template>
        </v-data-table>
      </v-card>
    </v-responsive>
  </v-container>
</template>

<script>
import { reactive, onMounted } from 'vue';
import axios from 'axios';
import AlertComponent from '../components/Alert.vue';
import { useRouter } from 'vue-router';
export default {
  components: {
    AlertComponent,
  },
  setup() {
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
    const tableHeaders = reactive([
      { title: 'Zonos pavadinimas', key: 'name' },
      { title: 'Miestas', key: 'city' },
      { title: 'Apmokestinamas laikas', key: 'paying_time' },
      { title: 'Kaina', key: 'price' },
      { title: 'Informacija', key: 'information' },
      { title: '', key: '' },
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

    return {
      alert,
      data,
      tableHeaders,
      goToZone,
    };
  },
  data() {
    return {
      search: '',
    };
  },
};
</script>
