<template>
  <div id="map"></div>
  <ModalMap :show="dataModal.show" :fullData="dataModal.fullData" @update:show="dataModal.show = $event" @modalClosed="handleModalClosed" />
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import axios from 'axios';
import store from '../plugins/store';
import ModalMap from './ModalMap.vue';
export default {
  components: {
    ModalMap,
  },
  setup() {
    const data = reactive({
      map: null,
      mapData: {},
      isModalOpen: false,
      clickedZoneData: null,
    });
    const dataModal = reactive({ show: false, fullData: {} });
    mapboxgl.accessToken = process.env.MAP_BOX;

    onMounted(async () => {
      try {
        const response = await axios.get(`${process.env.APP_URL}/parking_zone`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        });

        if (response.status === 200) {
          data.mapData.parkingZones = response.data;
          initializeMap();
        }
      } catch (error) {
        const alert = {
          show: true,
          type: 'error',
          title: 'Klaida!',
          text: error.response ? error.response.data.message : 'Nenumatyta klaida',
          timeout: 10000,
        };
        store.commit('setAlert', alert);
      }
    });

    const initializeMap = () => {
      data.map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [23.891521, 54.878326],
        zoom: 12,
      });

      data.map.on('style.load', () => {
        addParkingZoneLayers();
      });
      data.map.on('click', handleMapClick);
    };

    const addParkingZoneLayers = () => {
      data.mapData.parkingZones.forEach((zone, index) => {
        const layerId = `parkingZone-${index}`;
        const switchedCoordinates = zone.location_polygon.map(([lng, lat]) => [lat, lng]);
        data.map.addSource(layerId, {
          type: 'geojson',
          data: {
            type: 'Feature',
            geometry: {
              type: 'Polygon',
              coordinates: [switchedCoordinates],
            },
            properties: {
              zoneData: zone,
            },
          },
        });

        data.map.addLayer({
          id: layerId,
          type: 'fill',
          source: layerId,
          layout: {},
          paint: {
            'fill-color': zone.colour,
            'fill-opacity': 0.3,
          },
        });

        data.map.addLayer({
          id: `${layerId}-outline`,
          type: 'line',
          source: layerId,
          layout: {},
          paint: {
            'line-color': '#000',
            'line-width': 1,
          },
        });
      });
    };

    const handleMapClick = (e) => {
      const features = data.map.queryRenderedFeatures(e.point, {
        layers: data.mapData.parkingZones.map((zone, index) => `parkingZone-${index}`),
      });
      if (features.length > 0) {
        data.clickedZoneData = features[0].properties.zoneData;
        console.log('Clicked Zone Data:', JSON.parse(features[0].properties.zoneData));
        dataModal.fullData = JSON.parse(features[0].properties.zoneData);

        dataModal.show = true;
      }
    };
    const handleModalClosed = () => {
      dataModal.show = false;
    };

    return {
      data,
      handleMapClick,
      handleModalClosed,
      dataModal,
    };
  },
};
</script>

<style>
#map {
  display: flex;
  height: 100vh;
}

#map button {
  position: absolute;
  top: 10px;
  left: 10px;
}
</style>
