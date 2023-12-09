<template>
  <div id="map"></div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import mapboxgl from 'mapbox-gl';
import '@mapbox/mapbox-gl-draw/dist/mapbox-gl-draw.css';

export default {
  props: {
    zoneData: {
      type: Object,
    },
  },
  setup(props, { emit }) {
    let map = null;
    let draw = null;
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
        zoom: 16,
      });

      map.on('style.load', () => {
        initializeDraw();
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
          'fill-color': zone.colour,
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
      map,
      handleMapClick,
      handleModalClosed,
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
