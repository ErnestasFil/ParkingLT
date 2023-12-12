<template>
  <div id="map"></div>
  <ModalMap :show="dataModal.show" :fullData="dataModal.fullData" :zoneData="dataModal.zoneData" :spaceFree="dataModal.spaceFree" @update:show="dataModal.show = $event" @modalClosed="handleModalClosed" @deleteSpace="removeSpace" />
  <ConfirmModal ref="confirmModalRef" />
</template>

<script>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import mapboxgl from 'mapbox-gl';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import ModalMap from './ModalSpace.vue';
import store from '../plugins/store';
import axios from 'axios';
import ConfirmModal from '../components/ConfirmModal.vue';
import { useToast } from 'vue-toastification';
export default {
  components: {
    ModalMap,
    ConfirmModal,
  },
  props: {
    zoneData: {
      type: Object,
    },
    spaceData: {
      type: Object,
    },
  },
  setup(props, { emit }) {
    const toast = useToast();
    const confirmModalRef = ref(null);
    let map = null;
    const zone = props.zoneData;
    let space = props.spaceData;
    const data = reactive({
      isModalOpen: false,
      clickedSpaceData: null,
    });
    const dataModal = reactive({ show: false, fullData: {}, zoneData: {}, spaceFree: false });
    const switchedCoordinates = zone.location_polygon.map(([lng, lat]) => [lat, lng]);
    mapboxgl.accessToken = process.env.MAP_BOX;
    const calculatePolygonCenter = (polygon) => {
      const numPoints = polygon.length;
      let x = 0;
      let y = 0;

      for (const point of polygon) {
        x += point[1];
        y += point[0];
      }

      return [x / numPoints, y / numPoints];
    };

    const initializeMap = () => {
      if (!props.zoneData) {
        return;
      }

      map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12?optimize=true',
        center: calculatePolygonCenter(props.zoneData.location_polygon),
        zoom: 16,
      });

      map.on('style.load', () => {
        initializeDraw();
        initializeSpaceDraw();
      });
      map.on('click', handleMapClick);
    };

    const initializeDraw = () => {
      map.addSource('parkingZone', {
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
        id: 'parkingZone',
        type: 'fill',
        source: 'parkingZone',
        layout: {},
        paint: {
          'fill-color': '#8edafa',
          'fill-opacity': 0.3,
        },
      });

      map.addLayer({
        id: 'parkingZone-outline',
        type: 'line',
        source: 'parkingZone',
        layout: {},
        paint: {
          'line-color': '#000',
          'line-width': 1,
        },
      });
    };
    const initializeSpaceDraw = () => {
      space.forEach((space, index) => {
        const layerId = `parkingSpace-${space.id}`;
        const switchedCoordinates = space.location_polygon.map(([lng, lat]) => [lat, lng]);
        map.addSource(layerId, {
          type: 'geojson',
          data: {
            type: 'Feature',
            geometry: {
              type: 'Polygon',
              coordinates: [switchedCoordinates],
            },
            properties: {
              spaceData: space,
            },
          },
        });

        map.addLayer({
          id: layerId,
          type: 'fill',
          source: layerId,
          layout: {},
          paint: {
            'fill-color': space.invalid_place ? '#1b0ecf' : '#0ecf1e',
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

    onMounted(() => {
      initializeMap();
    });

    watch(
      () => props.zoneData,
      (newVal, oldVal) => {
        initializeMap();
        console.log('ZoneData changed:', newVal);
      }
    );
    const handleMapClick = (e) => {
      const features = map.queryRenderedFeatures(e.point, {
        layers: space.map((zone, index) => `parkingSpace-${zone.id}`),
      });
      if (features.length > 0) {
        data.clickedSpaceData = features[0].properties.spaceData;

        dataModal.fullData = JSON.parse(features[0].properties.spaceData);
        dataModal.zoneData = zone;
        if (isAuthenticated.value) {
          console.log();
          getReservations();
        }
        dataModal.show = true;
      }
    };

    const getReservations = async () => {
      try {
        await axios
          .get(`${process.env.APP_URL}/parking_zone/${dataModal.zoneData.id}/parking_space/${dataModal.fullData.id}/reservation`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          })
          .then((resData) => {
            if (resData.status === 200) {
              dataModal.spaceFree = isParkingSpaceFree(resData.data);
            }
          });
      } catch (error) {
        toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
          timeout: 10000,
        });
      }
    };
    function isParkingSpaceFree(reservationData) {
      if (reservationData.length === 0) {
        return true;
      }

      const newestReservation = reservationData.reduce((prev, current) => {
        return new Date(current.date_until) > new Date(prev.date_until) ? current : prev;
      });

      const now = new Date();
      const reservationEndTime = new Date(newestReservation.date_until);

      return now > reservationEndTime;
    }
    const handleModalClosed = () => {
      dataModal.show = false;
    };
    const removeSpace = async (zone, spaceId) => {
      const confirmation = await confirmModalRef.value.open(
        'Parkavimo vietos pašalinimas',
        'Ar tikrai norite pašalinti stovėjimo vietą gatvėje - ' + dataModal.fullData.street + ' numeris ' + dataModal.fullData.space_number + ' ir visą su ja susijusią informaciją?'
      );
      if (confirmation) {
        try {
          console.log(store.state.login.token);
          const response = await axios.delete(`${process.env.APP_URL}/parking_zone/${zone}/parking_space/${spaceId}`, {
            headers: {
              'Content-Type': 'application/json',
              Accept: '*/*',
              Authorization: `Bearer ${store.state.login.token}`,
            },
          });

          if (response.status === 204) {
            toast.error('Stovėjimo vieta pašalinta sėkmingai!', {
              timeout: 10000,
            });
            space = space.filter((spaceItem) => spaceItem.id !== spaceId);
            const layerId = `parkingSpace-${spaceId}`;
            const sourceId = layerId;
            map.removeLayer(layerId);
            map.removeLayer(`${layerId}-outline`);
            map.removeSource(sourceId);
          }
        } catch (error) {
          toast.error(error.response ? error.response.data.message : 'Nenumatyta klaida', {
            timeout: 10000,
          });
        }
      }
    };
    const isAuthenticated = computed(() => {
      return store.getters.isAuthenticated;
    });
    return {
      map,
      handleMapClick,
      handleModalClosed,
      removeSpace,
      dataModal,
      confirmModalRef,
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
