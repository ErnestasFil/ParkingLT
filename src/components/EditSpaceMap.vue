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
import refresh from '../plugins/refreshToken';
export default {
  props: {
    zoneData: {
      type: Object,
    },
  },
  setup(props, { emit }) {
    const router = useRouter();
    const toast = useToast();
    mapboxgl.accessToken = process.env.MAP_BOX;
    let map = null;
    let draw = null;
    const data = reactive({
      mapData: {},
    });
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
        center: calculatePolygonCenter(props.zoneData.space.location_polygon),
        zoom: 20,
      });

      map.on('style.load', () => {
        initializeDraw();
        addParkingSpaceLayers();
        addParkingZoneLayer();
      });
    };
    const addParkingSpaceLayers = () => {
      data.mapData.parkingSpaces.forEach((zone, index) => {
        if (zone.id !== props.zoneData.space.id) {
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
              'fill-color': '#e9c8f7',
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
    const addParkingZoneLayer = () => {
      const layerId = `parkingZone`;
      const switchedCoordinates = props.zoneData.zone.location_polygon.map(([lng, lat]) => [lat, lng]);
      map.addSource(layerId, {
        type: 'geojson',
        data: {
          type: 'Feature',
          geometry: {
            type: 'Polygon',
            coordinates: [switchedCoordinates],
          },
          properties: {
            zoneData: props.zoneData.zone,
          },
        },
      });

      map.addLayer({
        id: layerId,
        type: 'fill',
        source: layerId,
        layout: {},
        paint: {
          'fill-color': '#c8e9f7',
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
      draw = new MapboxDraw({
        displayControlsDefault: false,
        controls: {
          polygon: false,
          trash: false,
        },
      });
      map.addControl(draw, 'top-left');
      map.on('load', () => {
        const switchedCoordinates = props.zoneData.space.location_polygon.map(([lng, lat]) => [lat, lng]);
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
        .get(`${process.env.APP_URL}/parking_zone/${props.zoneData.zone.id}/parking_space`, {
          headers: {
            'Content-Type': 'application/json',
            Accept: '*/*',
          },
        })
        .then((response) => {
          if (response.status === 200) {
            data.mapData.parkingSpaces = response.data;
            initializeMap();
          }
        })
        .catch((error) => {
          refresh.error403(error, router);
          refresh.error404(error, router);
          refresh.errorOther(error, router);
        });
    });

    watch(
      () => props.zoneData,
      () => {
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
  height: 90vh;
  margin: auto;
}

#map button {
  position: absolute;
  top: 10px;
  left: 10px;
}
</style>
