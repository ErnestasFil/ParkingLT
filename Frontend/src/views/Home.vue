<template>
  <v-container class="fill-height">
    <v-responsive class="align-center fill-height">
      <MapComponent></MapComponent>
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
