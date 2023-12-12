<template>
  <Loader v-if="data.overlay" />
  <div id="map"></div>
  <ModalMap :show="dataModal.show" :fullData="dataModal.fullData" @update:show="dataModal.show = $event" @modalClosed="handleModalClosed" />
</template>

<script>
import { reactive, onMounted } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import axios from 'axios';
import ModalMap from './ModalMap.vue';
import { useToast } from 'vue-toastification';
import Loader from '../layouts/default/Loading.vue';
import { useRouter } from 'vue-router';
export default {
  components: {
    ModalMap,
    Loader,
  },
  setup() {
    const toast = useToast();
    const router = useRouter();
    const data = reactive({
      mapData: {},
      isModalOpen: false,
      clickedZoneData: null,
      overlay: true,
    });
    let map = null;
    const dataModal = reactive({ show: false, fullData: {} });
    mapboxgl.accessToken = process.env.MAP_BOX;

    onMounted(async () => {
      await axios
        .get(`${process.env.APP_URL}/parking_zone`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        })
        .then((response) => {
          if (response.status === 200) {
            data.mapData.parkingZones = response.data;
            initializeMap();
          }
        })
        .catch((error) => {
          if (error.response && error.response.status === 403) {
            toast.error('Prieiga negalima!', {
              timeout: 10000,
            });
            router.push({ name: 'Home' });
          } else if (error.response && error.response.status === 404) {
            toast.error(error.response.data.message, {
              timeout: 10000,
            });
            router.push({ name: 'Home' });
          } else {
            toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
              timeout: 10000,
            });
          }
        });

      setTimeout(() => {
        data.overlay = false;
      }, 1000);
    });

    const initializeMap = () => {
      map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [23.891521, 54.878326],
        zoom: 12,
      });

      map.on('style.load', () => {
        addParkingZoneLayers();
      });
      map.on('click', handleMapClick);
    };

    const addParkingZoneLayers = () => {
      data.mapData.parkingZones.forEach((zone, index) => {
        const layerId = `parkingZone-${index}`;
        const switchedCoordinates = zone.location_polygon.map(([lng, lat]) => [lat, lng]);
        map.addSource(layerId, {
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

        map.addLayer({
          id: layerId,
          type: 'fill',
          source: layerId,
          layout: {},
          paint: {
            'fill-color': zone.colour,
            'fill-opacity': 0.3,
          },
        });

        map.addLayer({
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
      const features = map.queryRenderedFeatures(e.point, {
        layers: data.mapData.parkingZones.map((zone, index) => `parkingZone-${index}`),
      });
      if (features.length > 0) {
        data.clickedZoneData = features[0].properties.zoneData;
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
