<template>
  <div id="map"></div>
</template>

<script>
import { onMounted, reactive } from 'vue';
import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import store from '../plugins/store';
import axios from 'axios';
export default {
  props: {
    zoneData: {
      type: Object,
    },
  },
  setup(props, { emit }) {
    let draw = null;
    mapboxgl.accessToken = process.env.MAP_BOX;

    let map = null;
    const data = reactive({
      mapData: {},
    });
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
        console.log(error);
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
      map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [23.891521, 54.878326],
        zoom: 8,
      });

      map.on('style.load', () => {
        initializeDraw();
        addParkingZoneLayers();
      });
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

    const initializeDraw = () => {
      draw = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
          polygon: false,
          trash: false,
        },
        defaultMode: 'draw_polygon',
      });
      map.addControl(draw, 'top-left');
      map.on('draw.delete', updateArea);
      map.on('draw.update', updateArea);
      map.on('draw.create', updateArea);
    };

    const updateArea = (e) => {
      const data = draw.getAll();
      const newPolygon = data.features[0].geometry.coordinates[0];
      const switched = newPolygon.map(([lat, lng]) => [lng, lat]);
      emit('createPolygon', switched);
    };

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
  top: 10px;
  left: 10px;
  background-color: white;
}
</style>
