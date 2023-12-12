<template>
  <div id="map"></div>
</template>

<script>
import { reactive, onMounted, watch } from 'vue';
import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { useRouter } from 'vue-router';
export default {
  props: {
    zoneData: {
      type: Object,
    },
  },
  setup(props, { emit }) {
    const router = useRouter();
    const toast = useToast();
    let map = null;
    let draw = null;
    const data = reactive({
      mapData: {},
    });
    const zone = props.zoneData;
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
        zoom: 14,
      });

      map.on('style.load', () => {
        initializeDraw();
        addParkingZoneLayers();
      });
    };
    const addParkingZoneLayers = () => {
      data.mapData.parkingZones.forEach((zone, index) => {
        if (zone.id !== props.zoneData.id) {
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
              'fill-opacity': 0.7,
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
        }
      });
    };
    const initializeDraw = () => {
      draw = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
          polygon: false,
          trash: false,
        },
      });
      map.addControl(draw, 'top-left');
      map.on('load', () => {
        draw.add({
          id: 'edit',
          type: 'Feature',
          geometry: {
            type: 'Polygon',
            coordinates: [switchedCoordinates],
          },
        });
      });
      map.on('draw.delete', updateArea);
      map.on('draw.update', updateArea);
    };

    const updateArea = (e) => {
      const data = draw.getAll();
      if (data.features.length > 0) {
        const newPolygon = data.features[0].geometry.coordinates[0];
        const switched = newPolygon.map(([lat, lng]) => [lng, lat]);
        emit('updatePolygon', switched);
      }
    };

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
    });

    watch(
      () => props.zoneData,
      (newVal, oldVal) => {
        initializeMap();
      }
    );

    return {
      map,
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
