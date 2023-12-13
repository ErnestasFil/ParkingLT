<template>
  <v-row justify="center">
    <v-dialog v-model="isModalOpen" max-width="600" persistent>
      <v-card>
        <v-toolbar>
          <v-card-title>
            <span class="text"> <span class="mdi mdi-map"></span> <b>Parkavimosi zona</b> - {{ fullData.name }} </span>
          </v-card-title>
        </v-toolbar>
        <v-card-text>
          <v-alert border="start" variant="tonal" title="Informacija">{{ fullData.information }} </v-alert>
          <v-table height="250px" class="pt-4 mx-lg-auto">
            <tbody>
              <tr>
                <td>Zonos pavadinimas:</td>
                <td>{{ fullData.name }}</td>
              </tr>
              <tr>
                <td>Miestas:</td>
                <td>{{ fullData.city }}</td>
              </tr>
              <tr>
                <td>Apmokestinamas laikas:</td>
                <td>{{ fullData.paying_time }} min</td>
              </tr>
              <tr>
                <td>Kaina:</td>
                <td>{{ typeof fullData.price !== 'undefined' ? fullData.price.toFixed(2) + ' €' : 'N/A' }}</td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
        <v-btn block outlined class="flex-grow-1" variant="tonal" @click="openMap(fullData.id)"> Parkavimo vietos zonoje </v-btn>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="closeModal"> Uždaryti </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-row>
</template>

<script>
export default {
  props: {
    show: Boolean,
    fullData: Object,
  },
  data() {
    return {
      isModalOpen: false,
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
    openMap(data) {
      this.$router.push({ name: 'ParkingSpace', params: { id: data } });
    },
  },
};
</script>
