<template>
  <div id="map"></div>
</template>

<script>
import { onMounted, reactive, watch, toRefs } from 'vue';
import mapboxgl from 'mapbox-gl';
import MapboxDraw from '@mapbox/mapbox-gl-draw';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';
import store from '../plugins/store';
import axios from 'axios';
export default {
  props: {
    zoneId: {
      type: String,
    },
    zoneData: {
      type: Object,
      default: () => ({}),
    },
  },
  setup(props, { emit }) {
    watch(
      () => props.zoneData,
      (newZoneData) => {
        if (newZoneData !== null) {
          addSpace(newZoneData);
          console.log('Received new zoneData:', newZoneData);
        }
      }
    );
    let draw = null;
    mapboxgl.accessToken = process.env.MAP_BOX;
    let map = null;
    let data = reactive({});

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
    onMounted(async () => {
      try {
        const response = await axios.get(`${process.env.APP_URL}/parking_zone/${props.zoneId}`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        });

        if (response.status === 200) {
          data.parkingZone = response.data;
          await axios
            .get(`${process.env.APP_URL}/parking_zone/${props.zoneId}/parking_space`, {
              headers: {
                'Content-Type': 'application/json',
                Accept: '*/*',
              },
            })
            .then((response) => {
              data.parkingSpaces = response;
            });
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
        center: calculatePolygonCenter(data.parkingZone.location_polygon),
        zoom: 16,
      });

      map.on('style.load', () => {
        initializeDraw();
        addParkingSpaceLayers();
      });
    };
    const addParkingSpaceLayers = () => {
      data.parkingSpaces.data.forEach((zone, index) => {
        const layerId = `parkingSpace-${index}`;
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
            'fill-color': zone.invalid_place ? '#1b0ecf' : '#0ecf1e',
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
    const addSpace = (data) => {
      const layerId = `parkingSpace-${Math.random()}`;
      const switchedCoordinates = data.location_polygon.map(([lng, lat]) => [lat, lng]);
      map.addSource(layerId, {
        type: 'geojson',
        data: {
          type: 'Feature',
          geometry: {
            type: 'Polygon',
            coordinates: [switchedCoordinates],
          },
          properties: {
            zoneData: data,
          },
        },
      });

      map.addLayer({
        id: layerId,
        type: 'fill',
        source: layerId,
        layout: {},
        paint: {
          'fill-color': data.invalid_place ? '#1b0ecf' : '#0ecf1e',
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
    };

    const initializeDraw = () => {
      const switchedCoordinates = data.parkingZone.location_polygon.map(([lng, lat]) => [lat, lng]);
      map.addSource('parkingZone', {
        type: 'geojson',
        data: {
          type: 'Feature',
          geometry: {
            type: 'Polygon',
            coordinates: [switchedCoordinates],
          },
          properties: {
            zoneData: data.parkingZone,
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
