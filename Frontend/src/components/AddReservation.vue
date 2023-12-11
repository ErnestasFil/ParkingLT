<template>
  <v-row justify="center">
    <v-dialog v-model="opened" min-width="600" max-width="900" @keydown.esc="cancel">
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text-h5"> <span class="mdi mdi-map"></span> <b>Rezervacija</b> </span>
          </v-card-title>
        </v-toolbar>
        <v-row no-gutters>
          <v-col cols="6">
            <v-sheet class="pa-2 ma-2"
              ><v-card-text>
                <v-alert border="start" variant="tonal" title="Parkavimo vietos informacija"> {{ space.information }} </v-alert>
                <v-table class="pt-4 mx-lg-auto">
                  <tbody>
                    <tr>
                      <td>Zonos pavadinimas:</td>
                      <td>{{ zone.name }}</td>
                    </tr>
                    <tr>
                      <td>Miestas:</td>
                      <td>{{ zone.city }}</td>
                    </tr>
                    <tr>
                      <td>Gatvė:</td>
                      <td>{{ space.street }}</td>
                    </tr>
                    <tr>
                      <td>Stovėjimo vietos numeris:</td>
                      <td>{{ space.space_number }}</td>
                    </tr>
                    <tr>
                      <td>Neįgaliojo vieta:</td>
                      <td>{{ space.invalid_place ? 'Taip' : 'Ne' }}</td>
                    </tr>
                    <tr>
                      <td>Apmokestinamas laikas:</td>
                      <td>{{ zone.paying_time }} min</td>
                    </tr>
                    <tr>
                      <td>Kaina:</td>
                      <td>{{ typeof zone.price !== 'undefined' ? zone.price.toFixed(2) + ' €' : 'N/A' }}</td>
                    </tr>
                  </tbody>
                </v-table>
                <v-alert border="start" v-if="!isAuthenticated" variant="tonal">Norėdami rezervuoti vietą prašome prisijungti</v-alert>
              </v-card-text>
            </v-sheet>
          </v-col>

          <v-col cols="6">
            <v-sheet class="pa-2 ma-2">
              <v-card-text>
                <v-alert border="start" variant="tonal" type="success" title="Rezervacijos pridėjimas" class="mb-10 mx-lg-auto"> </v-alert>
                <v-table class="mx-lg-auto">
                  <tbody>
                    <tr>
                      <td>Pradžios laikas:</td>
                      <td>{{ currentDateTime }}</td>
                    </tr>
                    <tr>
                      <td>Pabaigos laikas:</td>
                      <td>{{ endTime }}</td>
                    </tr>
                    <tr>
                      <td>Mokama suma:</td>
                      <td>{{ price.toFixed(2) }} €</td>
                    </tr>
                  </tbody>
                </v-table>
                <v-select v-model="selectedTime" class="mt-4" label="Pasirinkite rezervacijos trukmę" :items="timeOptions"></v-select>
              </v-card-text>
            </v-sheet>
          </v-col>
        </v-row>
        <v-btn v-if="isAuthenticated" block outlined class="flex-grow-1" variant="tonal" @click.native="agree"> Rezervuoti stovėjimo vietą </v-btn>
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
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
export default {
  data() {
    return {
      opened: false,
      zone: {},
      space: {},
      resolve: null,
      reject: null,
      selectedTime: null,
      timeOptions: [],
      currentDateTime: null,
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
    };
  },
  watch: {
    'zone.paying_time': {
      handler(newVal) {
        this.generateTimeOptions(newVal);
      },
      immediate: true,
    },
    selectedTime: {
      handler() {
        const now = new Date();
        const [selectedHours, selectedMinutes] = this.selectedTime.split(':').map(Number);
        const newDateTime = new Date(now);
        newDateTime.setHours(newDateTime.getHours() + selectedHours, newDateTime.getMinutes() + selectedMinutes);
        this.endTime = new Intl.DateTimeFormat('lt-LT', this.options).format(newDateTime);
        this.price = ((selectedHours * 60 + selectedMinutes) / this.zone.paying_time) * this.zone.price;
      },
    },
  },
  mounted() {
    setInterval(() => {
      const now = new Date();
      this.currentDateTime = new Intl.DateTimeFormat('lt-LT', this.options).format(now);
      if (this.selectedTime) {
        const [selectedHours, selectedMinutes] = this.selectedTime.split(':').map(Number);
        const newDateTime = new Date(now);
        newDateTime.setHours(newDateTime.getHours() + selectedHours, newDateTime.getMinutes() + selectedMinutes);
        this.endTime = new Intl.DateTimeFormat('lt-LT', this.options).format(newDateTime);
      } else {
        this.endTime = this.currentDateTime;
      }
    }, 500);
  },
  methods: {
    open(zone, space) {
      this.opened = true;
      this.zone = zone;
      this.space = space;
      return new Promise((resolve, reject) => {
        this.resolve = resolve;
        this.reject = reject;
      });
    },
    agree() {
      this.resolve(true);
      this.opened = false;
      const [selectedHours, selectedMinutes] = this.selectedTime.split(':').map(Number);
      this.$emit('sendData', selectedHours * 60 + selectedMinutes);
    },
    cancel() {
      this.resolve(false);
      this.opened = false;
    },
    generateTimeOptions(payingTime) {
      this.timeOptions = [];
      const maxDuration = 24 * 60;
      for (let i = payingTime; i <= maxDuration; i += payingTime) {
        const hours = Math.floor(i / 60);
        const minutes = i % 60;
        const formattedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;

        this.timeOptions.push(formattedTime);
      }
      this.selectedTime = this.timeOptions[0];
    },
  },
  setup() {
    const isAuthenticated = computed(() => {
      return store.getters.isAuthenticated;
    });
    const isAdmin = computed(() => {
      return store.getters.isAdmin;
    });
    const router = useRouter();
    return {
      isAuthenticated,
      isAdmin,
      router,
    };
  },
};
</script>
