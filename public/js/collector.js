// Collector dashboard: draw today's route on the small map.
var pageData = readPageData();
var map = makeMap('map');
var route = pageData.route;

if (route && route.path.length > 1) {
    var line = L.polyline(route.path, { color: route.color, weight: 6 }).addTo(map);
    for (var i = 0; i < route.path.length; i++) {
        L.marker(route.path[i]).addTo(map);
    }
    fitLayers(map, [line]);
}
