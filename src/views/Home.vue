<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <v-sheet class="pa-2 ma-2"><MapComponent></MapComponent></v-sheet>
    </v-responsive>
  </v-container>
</template>

<script>
import MapComponent from '../components/ParkingZoneMap.vue';
export default {
  components: {
    MapComponent,
  },
};
</script>
